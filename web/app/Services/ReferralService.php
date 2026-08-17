<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ReferralClick;
use App\Models\ReferralCommission;
use App\Models\Tenant;
use Illuminate\Support\Str;

class ReferralService
{
    public function __construct(
        protected WalletService $walletService,
        protected ActivityLogService $activityLog,
    ) {}

    public function ensureCode(Tenant $tenant): string
    {
        if (filled($tenant->referral_code)) {
            return (string) $tenant->referral_code;
        }

        do {
            $code = Str::upper(Str::random(8));
        } while (Tenant::query()->where('referral_code', $code)->exists());

        $tenant->update(['referral_code' => $code]);

        return $code;
    }

    public function trackClick(string $code, ?string $ip = null, ?string $userAgent = null): ?Tenant
    {
        $tenant = Tenant::query()->where('referral_code', Str::upper($code))->first();
        if (! $tenant) {
            return null;
        }

        ReferralClick::query()->create([
            'tenant_id' => $tenant->id,
            'code' => $tenant->referral_code,
            'ip' => $ip,
            'user_agent' => $userAgent ? mb_substr($userAgent, 0, 255) : null,
        ]);

        return $tenant;
    }

    public function attachToNewTenant(Tenant $tenant, ?string $code): void
    {
        $code = Str::upper(trim((string) $code));
        if ($code === '') {
            return;
        }

        $referrer = Tenant::query()
            ->where('referral_code', $code)
            ->whereKeyNot($tenant->id)
            ->first();

        if (! $referrer) {
            return;
        }

        $tenant->update(['referred_by_tenant_id' => $referrer->id]);
        $this->activityLog->record('referral_signup', Tenant::class, $tenant->id, [
            'referrer_id' => $referrer->id,
            'code' => $code,
        ]);
    }

    public function creditFromPaidOrder(Order $order): void
    {
        $from = $order->store?->tenant;
        $referrerId = $from?->referred_by_tenant_id;
        if (! $from || ! $referrerId) {
            return;
        }

        if (ReferralCommission::query()->where('order_id', $order->id)->exists()) {
            return;
        }

        $rate = (float) config('referral.commission_rate', 0.05);
        $max = (int) config('referral.commission_max', 50000);
        $amount = (int) min($max, max(0, round(((float) $order->total_amount) * $rate)));
        if ($amount < 1) {
            return;
        }

        $wallet = $this->walletService->ensureForTenant($referrerId);
        $this->walletService->creditReferral($wallet->id, $amount, $order->id);

        ReferralCommission::query()->create([
            'tenant_id' => $referrerId,
            'from_tenant_id' => $from->id,
            'order_id' => $order->id,
            'amount' => $amount,
            'status' => 'credited',
        ]);

        $this->activityLog->record('referral_commission', Order::class, $order->id, [
            'amount' => $amount,
            'referrer_id' => $referrerId,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(Tenant $tenant): array
    {
        $code = $this->ensureCode($tenant);
        $clicks = ReferralClick::query()->where('tenant_id', $tenant->id)->count();
        $signups = Tenant::query()->where('referred_by_tenant_id', $tenant->id)->count();
        $commissions = ReferralCommission::query()->where('tenant_id', $tenant->id)->get();

        return [
            'code' => $code,
            'url' => url('/register?ref='.$code),
            'clicks' => $clicks,
            'signups' => $signups,
            'earned' => (int) $commissions->sum('amount'),
            'commissions' => $commissions->map(fn (ReferralCommission $c) => [
                'amount' => $c->amount,
                'order_id' => $c->order_id,
                'status' => $c->status,
                'created_at' => $c->created_at?->format('d M Y'),
            ])->values()->all(),
        ];
    }
}
