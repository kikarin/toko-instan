<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\URL;

class OrderTrackingService
{
    public function signedUrl(Order $order, int $expiresDays = 90): string
    {
        $order->loadMissing('store');

        $storeSlug = $order->store?->slug;

        if ($storeSlug === null || $storeSlug === '') {
            throw new \RuntimeException('Toko pesanan tidak ditemukan.');
        }

        return URL::temporarySignedRoute(
            'orders.track',
            now()->addDays($expiresDays),
            [
                'store_slug' => $storeSlug,
                'orderNumber' => $order->order_number,
            ],
        );
    }

    public function findForGuestLookup(string $storeSlug, string $orderNumber, string $email): ?Order
    {
        return Order::query()
            ->where('order_number', $orderNumber)
            ->whereRaw('LOWER(customer_email) = ?', [strtolower($email)])
            ->whereHas('store', fn ($query) => $query->where('slug', $storeSlug))
            ->first();
    }
}
