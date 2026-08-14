<?php

namespace App\Services;

use App\DTO\WebhookPaymentResult;
use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Gateways\MidtransGateway;
use App\Mail\SubscriptionExpiredMail;
use App\Mail\SubscriptionRenewalMail;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

class SubscriptionService
{
    public const ORDER_PREFIX = 'SUB-';

    public function __construct(
        protected MidtransGateway $midtransGateway
    ) {}

    public function planByCode(string $code): ?Plan
    {
        return Plan::query()->where('code', $code)->where('is_active', true)->first();
    }

    public function premiumPlan(): Plan
    {
        return $this->planByCode('premium')
            ?? throw new RuntimeException('Paket Premium belum terdaftar.');
    }

    public function freePlan(): Plan
    {
        return $this->planByCode('free')
            ?? throw new RuntimeException('Paket Free belum terdaftar.');
    }

    public function isPremium(Tenant $tenant): bool
    {
        $code = (string) $tenant->plan;

        if (in_array($code, ['premium', 'pro', 'enterprise'], true)) {
            $subscription = $tenant->activeSubscription;

            if ($subscription && $subscription->ends_at && $subscription->ends_at->isPast()) {
                return false;
            }

            return true;
        }

        $subscription = $tenant->activeSubscription;

        return $subscription?->isActive() === true
            && $subscription->plan?->isPremium() === true;
    }

    public function usesDirectSettlement(Tenant $tenant): bool
    {
        if (! $this->isPremium($tenant)) {
            return false;
        }

        $plan = $this->planByCode((string) $tenant->plan);

        return $plan?->usesDirectSettlement() ?? true;
    }

    public function withdrawFeeFor(Tenant $tenant): int
    {
        $plan = $this->planByCode((string) $tenant->plan);

        if ($plan) {
            return (int) $plan->withdraw_fee;
        }

        return $this->isPremium($tenant) ? 0 : 5000;
    }

    /**
     * @return array<string, mixed>
     */
    public function summaryFor(Tenant $tenant): array
    {
        $tenant->loadMissing('activeSubscription.plan');
        $subscription = $tenant->activeSubscription;
        $premium = $this->isPremium($tenant);

        return [
            'plan_code' => $premium ? 'premium' : 'free',
            'plan_name' => $premium ? 'Premium' : 'Free',
            'is_premium' => $premium,
            'settlement_mode' => $this->usesDirectSettlement($tenant) ? 'direct' : 'escrow',
            'withdraw_fee' => $this->withdrawFeeFor($tenant),
            'ends_at' => $subscription?->ends_at?->timezone(config('app.timezone'))->format('d M Y H:i'),
            'status' => $subscription?->status->value,
        ];
    }

    public function initiateUpgrade(Tenant $tenant): SubscriptionPayment
    {
        $plan = $this->premiumPlan();
        $user = $tenant->user;
        $store = $tenant->stores()->first();

        $pending = SubscriptionPayment::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', PaymentStatus::Pending)
            ->latest('id')
            ->first();

        if ($pending?->snap_token) {
            return $pending;
        }

        $orderId = self::ORDER_PREFIX.$tenant->id.'-'.Str::upper(Str::random(8));

        $snap = $this->midtransGateway->createSnap(
            orderId: $orderId,
            grossAmount: (int) $plan->price,
            customer: [
                'first_name' => $user?->name ?? $tenant->name,
                'email' => $user?->email ?? '',
                'phone' => $store?->phone ?? $user?->phone ?? '',
            ],
            method: PaymentMethod::Qris,
            finishUrl: url('/subscription'),
        );

        return SubscriptionPayment::query()->create([
            'tenant_id' => $tenant->id,
            'provider' => PaymentProvider::Midtrans,
            'amount' => $plan->price,
            'status' => PaymentStatus::Pending,
            'external_id' => $orderId,
            'idempotency_key' => 'sub-init:'.$orderId,
            'snap_token' => $snap->snapToken,
            'redirect_url' => $snap->redirectUrl,
            'payload' => $snap->meta,
        ]);
    }

    public function applyGatewayResult(WebhookPaymentResult $result): SubscriptionPayment
    {
        return DB::transaction(function () use ($result) {
            $existing = SubscriptionPayment::query()
                ->where('idempotency_key', $result->idempotencyKey)
                ->first();

            if ($existing?->isPaid()) {
                return $existing;
            }

            $payment = $existing ?? SubscriptionPayment::query()
                ->where('external_id', $result->orderNumber)
                ->latest('id')
                ->firstOrFail();

            $payment->forceFill([
                'external_id' => $result->externalId ?: $result->orderNumber,
                'idempotency_key' => $result->idempotencyKey,
                'payload' => $result->payload,
            ]);

            if ($result->isPaid) {
                $payment->status = PaymentStatus::Paid;
                $payment->paid_at = now();
                $payment->save();

                $subscription = $this->activatePremium($payment->tenant()->firstOrFail());
                $payment->update(['subscription_id' => $subscription->id]);

                return $payment->fresh();
            }

            if (in_array($result->gatewayStatus, ['deny', 'cancel', 'failure'], true)) {
                $payment->status = PaymentStatus::Failed;
            } elseif ($result->gatewayStatus === 'expire') {
                $payment->status = PaymentStatus::Expired;
            }

            $payment->save();

            return $payment->fresh();
        });
    }

    public function syncPayment(SubscriptionPayment $payment): SubscriptionPayment
    {
        $orderId = (string) ($payment->payload['order_id'] ?? $payment->external_id);
        $payload = $this->midtransGateway->fetchTransactionStatus($orderId);

        if ((string) ($payload['status_code'] ?? '') === '404') {
            throw new RuntimeException('Transaksi langganan belum ditemukan di Midtrans.');
        }

        $result = $this->midtransGateway->parseStatusPayload($payload);

        return $this->applyGatewayResult($result);
    }

    public function activatePremium(Tenant $tenant, ?\DateTimeInterface $from = null): Subscription
    {
        $premium = $this->premiumPlan();
        $start = $from ? Carbon::parse($from) : now();

        return DB::transaction(function () use ($tenant, $premium, $start) {
            $current = Subscription::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', SubscriptionStatus::Active)
                ->where('plan_id', $premium->id)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($current && $current->ends_at && $current->ends_at->isFuture()) {
                $current->update([
                    'ends_at' => $current->ends_at->addMonth(),
                    'renewal_notified_at' => null,
                ]);
            } else {
                Subscription::query()
                    ->where('tenant_id', $tenant->id)
                    ->where('status', SubscriptionStatus::Active)
                    ->update(['status' => SubscriptionStatus::Cancelled->value]);

                $current = Subscription::query()->create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $premium->id,
                    'status' => SubscriptionStatus::Active,
                    'starts_at' => $start,
                    'ends_at' => $start->copy()->addMonth(),
                ]);
            }

            $tenant->update(['plan' => 'premium']);

            return $current->fresh(['plan']);
        });
    }

    public function expireOverdue(): int
    {
        $expired = Subscription::query()
            ->with(['tenant.user', 'plan'])
            ->where('status', SubscriptionStatus::Active)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->get();

        $count = 0;

        foreach ($expired as $subscription) {
            $this->downgradeToFree($subscription);
            $count++;
        }

        return $count;
    }

    public function notifyUpcomingRenewals(int $withinDays = 7): int
    {
        $until = now()->addDays($withinDays);

        $due = Subscription::query()
            ->with(['tenant.user', 'plan'])
            ->where('status', SubscriptionStatus::Active)
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [now(), $until])
            ->whereNull('renewal_notified_at')
            ->get();

        $count = 0;

        foreach ($due as $subscription) {
            $email = $subscription->tenant?->user?->email;
            if ($email) {
                Mail::to($email)->queue(new SubscriptionRenewalMail($subscription));
            }

            $subscription->update(['renewal_notified_at' => now()]);
            $count++;
        }

        return $count;
    }

    public function downgradeToFree(Subscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $subscription->update(['status' => SubscriptionStatus::Expired]);

            $tenant = $subscription->tenant;
            if ($tenant) {
                $tenant->update(['plan' => 'free']);
            }
        });

        $email = $subscription->tenant?->user?->email;
        if ($email) {
            Mail::to($email)->queue(new SubscriptionExpiredMail($subscription->fresh()));
            $user = $subscription->tenant?->user;
            if ($user) {
                app(SellerAlertService::class)->push(
                    $user,
                    'Langganan berakhir',
                    'Paket Premium sudah kedaluwarsa. Toko kembali ke Free.',
                    url('/subscription'),
                );
            }
        }
    }
}
