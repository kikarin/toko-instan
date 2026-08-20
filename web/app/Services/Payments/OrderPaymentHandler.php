<?php

namespace App\Services\Payments;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Mail;

/**
 * Handles a successful payment: settles the order through escrow (or
 * direct settlement for premium) and notifies the buyer.
 */
class OrderPaymentHandler
{
    public function __construct(protected OrderService $orderService) {}

    public function handle(Order $order): Order
    {
        $this->orderService->markOrderPaid($order);

        if ($order->customer_email) {
            Mail::to($order->customer_email)->queue(new OrderConfirmationMail($order));
        }

        return $order->fresh();
    }
}
