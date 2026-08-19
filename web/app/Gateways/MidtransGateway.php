<?php

namespace App\Gateways;

use App\Contracts\PaymentGateway;
use App\DTO\PaymentInitResult;
use App\DTO\WebhookPaymentResult;
use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransGateway implements PaymentGateway
{
    public function createPayment(Order $order, PaymentMethod $method): PaymentInitResult
    {
        return $this->createSnap(
            orderId: $order->order_number,
            grossAmount: (int) round((float) $order->total_amount),
            customer: [
                'first_name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
            ],
            method: $method,
            finishUrl: url('/'.$order->store?->slug.'/orders/'.$order->order_number.'/success'),
        );
    }

    /**
     * @param  array{first_name?: string, email?: string, phone?: string}  $customer
     */
    public function createSnap(
        string $orderId,
        int $grossAmount,
        array $customer,
        PaymentMethod $method,
        ?string $finishUrl = null,
    ): PaymentInitResult {
        $serverKey = (string) config('services.midtrans.server_key');
        if ($serverKey === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $customer['first_name'] ?? 'Seller',
                'email' => $customer['email'] ?? '',
                'phone' => $customer['phone'] ?? '',
            ],
            'enabled_payments' => $this->enabledPayments($method),
            'callbacks' => [
                'finish' => $finishUrl ?? url('/subscription'),
            ],
        ];

        $baseUrl = config('services.midtrans.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->asJson()
                ->post($baseUrl.'/snap/v1/transactions', $payload)
                ->throw()
                ->json();
        } catch (RequestException $e) {
            $body = $e->response?->json();
            $detail = is_array($body)
                ? (implode(' ', $body['error_messages'] ?? []) ?: ($body['status_message'] ?? ''))
                : '';

            throw new RuntimeException(
                trim('Gagal membuat transaksi Midtrans. '.($detail !== '' ? $detail : $e->getMessage())),
                0,
                $e,
            );
        }

        return new PaymentInitResult(
            provider: PaymentProvider::Midtrans->value,
            method: $method->value,
            status: PaymentStatus::Pending->value,
            snapToken: $response['token'] ?? null,
            redirectUrl: $response['redirect_url'] ?? null,
            externalId: $orderId,
            meta: $response,
        );
    }

    public function verifyWebhook(Request $request): bool
    {
        $serverKey = (string) config('services.midtrans.server_key');
        $orderId = (string) $request->input('order_id', '');
        $statusCode = (string) $request->input('status_code', '');
        $grossAmount = (string) $request->input('gross_amount', '');
        $signature = (string) $request->input('signature_key', '');

        if ($serverKey === '' || $orderId === '' || $signature === '') {
            return false;
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expected, $signature);
    }

    public function parseWebhook(Request $request): WebhookPaymentResult
    {
        $orderId = (string) $request->input('order_id');
        $transactionId = (string) $request->input('transaction_id', $orderId);
        $transactionStatus = (string) $request->input('transaction_status', '');
        $fraudStatus = (string) $request->input('fraud_status', 'accept');
        $statusCode = (string) $request->input('status_code', '');

        $isPaid = in_array($transactionStatus, ['capture', 'settlement'], true)
            && ($fraudStatus === 'accept' || $fraudStatus === '');

        if ($transactionStatus === 'capture' && $fraudStatus === 'challenge') {
            $isPaid = false;
        }

        return new WebhookPaymentResult(
            orderNumber: $orderId,
            externalId: $transactionId,
            idempotencyKey: 'midtrans:'.$transactionId.':'.$transactionStatus.':'.$statusCode,
            isPaid: $isPaid,
            gatewayStatus: $transactionStatus,
            payload: $request->all(),
        );
    }

    /**
     * Ask Midtrans for the current transaction status (used when webhook cannot reach localhost).
     *
     * @return array<string, mixed>
     */
    public function fetchTransactionStatus(string $orderNumber): array
    {
        $serverKey = (string) config('services.midtrans.server_key');
        if ($serverKey === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        }

        $baseUrl = config('services.midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';

        try {
            return Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->get($baseUrl.'/v2/'.$orderNumber.'/status')
                ->throw()
                ->json();
        } catch (RequestException $e) {
            throw new RuntimeException('Gagal cek status Midtrans: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function parseStatusPayload(array $payload): WebhookPaymentResult
    {
        $request = Request::create('/', 'POST', $payload);

        return $this->parseWebhook($request);
    }

    /**
     * @return list<string>
     */
    protected function enabledPayments(PaymentMethod $method): array
    {
        return match ($method) {
            PaymentMethod::Qris => ['qris', 'gopay', 'other_qris'],
            PaymentMethod::Va => [
                'bca_va',
                'bni_va',
                'bri_va',
                'permata_va',
                'other_va',
                'echannel',
            ],
            PaymentMethod::Ewallet => ['gopay', 'shopeepay', 'other_qris'],
            default => ['other_qris', 'bca_va', 'gopay'],
        };
    }
}
