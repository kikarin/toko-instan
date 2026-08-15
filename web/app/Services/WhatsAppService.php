<?php

namespace App\Services;

use App\Contracts\WhatsAppGateway;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Models\Withdrawal;
use Throwable;

class WhatsAppService
{
    public function __construct(
        protected WhatsAppGateway $gateway,
        protected SubscriptionService $subscriptionService,
    ) {}

    public function notifyNewOrder(Order $order): void
    {
        $store = $order->store;
        $seller = $store?->tenant?->user;
        if (! $store || ! $seller || ! $this->subscriptionService->isPremium($store->tenant)) {
            return;
        }

        $to = $this->destination($seller, $store);
        if ($to === null) {
            return;
        }

        $this->safeSend($to, "Pesanan baru {$order->order_number} di {$store->name}. Total Rp ".number_format((float) $order->total_amount, 0, ',', '.').'. Cek dashboard: '.url('/orders'));
    }

    public function notifyWithdrawalApproved(Withdrawal $withdrawal): void
    {
        $tenant = $withdrawal->tenant ?? $withdrawal->wallet?->tenant;
        $seller = $tenant?->user;
        if (! $tenant || ! $seller || ! $this->subscriptionService->isPremium($tenant)) {
            return;
        }

        $store = $tenant->stores()->first();
        $to = $this->destination($seller, $store);
        if ($to === null) {
            return;
        }

        $this->safeSend($to, 'Penarikan disetujui Rp '.number_format((float) $withdrawal->net_amount, 0, ',', '.').'. Cek dompet: '.url('/wallet'));
    }

    protected function destination(User $seller, ?Store $store): ?string
    {
        return $this->toE164((string) ($store?->phone ?: $seller->phone));
    }

    public function toE164(string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }
        if (! str_starts_with($digits, '62') || strlen($digits) < 10) {
            return null;
        }

        return $digits;
    }

    protected function safeSend(string $to, string $body): void
    {
        try {
            $this->gateway->sendText($to, $body);
        } catch (Throwable) {
        }
    }
}
