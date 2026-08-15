<?php

namespace App\Services;

use App\Events\SellerNotified;
use App\Mail\OrderCreatedMail;
use App\Mail\WithdrawalApprovedMail;
use App\Models\Order;
use App\Models\User;
use App\Models\Withdrawal;
use App\Notifications\SellerDashboardNotification;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SellerAlertService
{
    public function notifyNewOrder(Order $order): void
    {
        $seller = $order->store?->tenant?->user;
        if (! $seller) {
            return;
        }

        $title = 'Pesanan baru';
        $body = $order->order_number.' · Rp '.number_format((float) $order->total_amount, 0, ',', '.');

        $this->push($seller, $title, $body, url('/orders'));

        try {
            Mail::to($seller->email)->queue(new OrderCreatedMail($order));
        } catch (Throwable) {
        }

        try {
            app(WhatsAppService::class)->notifyNewOrder($order);
        } catch (Throwable) {
        }
    }

    public function notifyWithdrawalApproved(Withdrawal $withdrawal): void
    {
        $seller = $withdrawal->tenant?->user ?? $withdrawal->wallet?->tenant?->user;
        if (! $seller) {
            return;
        }

        $title = 'Penarikan disetujui';
        $body = 'Rp '.number_format((float) $withdrawal->net_amount, 0, ',', '.');

        $this->push($seller, $title, $body, url('/wallet'));

        try {
            Mail::to($seller->email)->queue(new WithdrawalApprovedMail($withdrawal));
        } catch (Throwable) {
        }

        try {
            app(WhatsAppService::class)->notifyWithdrawalApproved($withdrawal);
        } catch (Throwable) {
        }
    }

    public function push(User $seller, string $title, string $body, ?string $url = null): void
    {
        $seller->notify(new SellerDashboardNotification($title, $body, $url));

        try {
            SellerNotified::dispatch($seller->id, compact('title', 'body', 'url'));
        } catch (Throwable) {
        }
    }
}
