<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\DTO\WebhookPaymentResult;
use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Gateways\CodPaymentGateway;
use App\Gateways\ManualPaymentGateway;
use App\Gateways\MidtransGateway;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PaymentService
{
    public function __construct(
        protected OrderService $orderService,
        protected MidtransGateway $midtransGateway,
        protected ManualPaymentGateway $manualPaymentGateway,
        protected CodPaymentGateway $codPaymentGateway,
        protected SubscriptionService $subscriptionService,
    ) {}

    public function resolveGateway(PaymentMethod $method): PaymentGateway
    {
        return match ($method->provider()) {
            PaymentProvider::Midtrans => $this->midtransGateway,
            PaymentProvider::Manual => $this->manualPaymentGateway,
            PaymentProvider::Cod => $this->codPaymentGateway,
        };
    }

    public function initiate(Order $order, string $paymentMethod): Payment
    {
        $method = PaymentMethod::tryFrom($paymentMethod)
            ?? throw ValidationException::withMessages([
                'payment_method' => 'Metode pembayaran tidak valid.',
            ]);

        if ($method === PaymentMethod::Cod && $this->orderIsDigitalOnly($order)) {
            throw ValidationException::withMessages([
                'payment_method' => 'COD tidak tersedia untuk pesanan produk digital.',
            ]);
        }

        $gateway = $this->resolveGateway($method);
        $result = $gateway->createPayment($order, $method);

        $tenantId = $order->store?->tenant_id;

        $payment = Payment::query()->create([
            'tenant_id' => $tenantId,
            'order_id' => $order->id,
            'provider' => $result->provider,
            'method' => $result->method,
            'amount' => (int) round((float) $order->total_amount),
            'status' => $result->status,
            'external_id' => $result->externalId,
            'idempotency_key' => 'init:'.$order->order_number.':'.Str::uuid(),
            'snap_token' => $result->snapToken,
            'redirect_url' => $result->redirectUrl,
            'payload' => $result->meta,
        ]);

        $order->update([
            'payment_method' => $method->value,
        ]);

        return $payment;
    }

    public function handleMidtransWebhook(Request $request): Payment|SubscriptionPayment
    {
        if (! $this->midtransGateway->verifyWebhook($request)) {
            throw new RuntimeException('Signature Midtrans tidak valid.');
        }

        $result = $this->midtransGateway->parseWebhook($request);

        if (str_starts_with($result->orderNumber, SubscriptionService::ORDER_PREFIX)) {
            return $this->subscriptionService->applyGatewayResult($result);
        }

        return $this->applyGatewayResult($result);
    }

    /**
     * Pull latest Midtrans status via API — webhook cannot reach localhost.
     */
    public function syncMidtransStatus(Order $order): Payment
    {
        $payload = $this->midtransGateway->fetchTransactionStatus($order->order_number);

        if ((string) ($payload['status_code'] ?? '') === '404') {
            throw new RuntimeException('Transaksi Midtrans belum ditemukan.');
        }

        $result = $this->midtransGateway->parseStatusPayload($payload);

        return $this->applyGatewayResult($result);
    }

    public function applyGatewayResult(WebhookPaymentResult $result): Payment
    {
        return DB::transaction(function () use ($result) {
            $existing = Payment::query()
                ->where('idempotency_key', $result->idempotencyKey)
                ->first();

            if ($existing?->isPaid()) {
                return $existing;
            }

            $order = Order::query()
                ->where('order_number', $result->orderNumber)
                ->lockForUpdate()
                ->firstOrFail();

            $payment = $existing ?? Payment::query()
                ->where('order_id', $order->id)
                ->where('provider', PaymentProvider::Midtrans)
                ->latest('id')
                ->first();

            if (! $payment) {
                $payment = Payment::query()->create([
                    'tenant_id' => $order->store?->tenant_id,
                    'order_id' => $order->id,
                    'provider' => PaymentProvider::Midtrans,
                    'method' => PaymentMethod::tryFrom((string) $order->payment_method) ?? PaymentMethod::Qris,
                    'amount' => (int) round((float) $order->total_amount),
                    'status' => PaymentStatus::Pending,
                    'idempotency_key' => $result->idempotencyKey,
                ]);
            }

            $payment->forceFill([
                'external_id' => $result->externalId,
                'idempotency_key' => $result->idempotencyKey,
                'payload' => $result->payload,
            ]);

            if ($result->isPaid) {
                $payment->status = PaymentStatus::Paid;
                $payment->paid_at = now();
                $payment->save();

                $this->orderService->markOrderPaid($order->fresh(['store']));
                $order->fresh()->update(['paid_at' => now()]);
            } elseif (in_array($result->gatewayStatus, ['deny', 'cancel', 'failure'], true)) {
                $payment->status = PaymentStatus::Failed;
                $payment->save();
            } elseif ($result->gatewayStatus === 'expire') {
                $payment->status = PaymentStatus::Expired;
                $payment->save();
            } else {
                $payment->save();
            }

            return $payment->fresh();
        });
    }

    public function attachTransferProof(Payment $payment, string $path, string $name): Payment
    {
        if ($payment->provider !== PaymentProvider::Manual) {
            throw ValidationException::withMessages([
                'proof' => 'Upload bukti hanya untuk transfer manual.',
            ]);
        }

        if ($payment->isPaid()) {
            throw ValidationException::withMessages([
                'proof' => 'Pembayaran sudah dikonfirmasi.',
            ]);
        }

        $payment->update([
            'proof_path' => $path,
            'proof_name' => $name,
            'status' => PaymentStatus::WaitingConfirmation,
        ]);

        return $payment->fresh();
    }

    public function confirmManualOrCod(Payment $payment): Payment
    {
        if (! in_array($payment->provider, [PaymentProvider::Manual, PaymentProvider::Cod], true)) {
            throw ValidationException::withMessages([
                'payment' => 'Konfirmasi manual hanya untuk transfer/COD.',
            ]);
        }

        if ($payment->provider === PaymentProvider::Manual
            && $payment->status === PaymentStatus::Pending
            && blank($payment->proof_path)) {
            throw ValidationException::withMessages([
                'payment' => 'Buyer belum mengunggah bukti transfer.',
            ]);
        }

        return DB::transaction(function () use ($payment) {
            $locked = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->isPaid()) {
                return $locked;
            }

            $locked->forceFill([
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
            ])->save();

            $order = $locked->order()->with('store')->firstOrFail();
            $this->orderService->markOrderPaid($order);
            $order->update(['paid_at' => $order->paid_at ?? now()]);

            return $locked->fresh();
        });
    }

    public function latestForOrder(Order $order): ?Payment
    {
        return Payment::query()->where('order_id', $order->id)->latest('id')->first();
    }

    protected function orderIsDigitalOnly(Order $order): bool
    {
        $order->loadMissing('items');

        if ($order->items->isEmpty()) {
            return false;
        }

        return $order->items->every(fn ($item) => ($item->product_type ?? 'physical') === 'digital');
    }
}
