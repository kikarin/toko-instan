<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Services\Payments\Contracts\PaymentGateway;

class PaymentService
{
    public function __construct(protected PaymentGateway $gateway) {}

    /**
     * Initiate payment for an order and return the redirect URL.
     */
    public function initiate(Order $order): string
    {
        if ($order->status === 'paid' || $order->status === 'completed') {
            return route('orders.success', [
                'store_slug' => $order->store?->slug ?? '',
                'orderNumber' => $order->order_number,
            ]);
        }

        return $this->gateway->createPayment($order);
    }

    /**
     * Confirm a payment succeeded and settle the order.
     */
    public function markPaid(Order $order): Order
    {
        return app(OrderPaymentHandler::class)->handle($order);
    }
}
