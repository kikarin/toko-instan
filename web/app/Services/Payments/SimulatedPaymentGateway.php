<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Services\Payments\Contracts\PaymentGateway;

/**
 * Simulated payment gateway for development. Redirects the buyer to an
 * internal page where they can confirm the payment immediately.
 */
class SimulatedPaymentGateway implements PaymentGateway
{
    public function createPayment(Order $order): string
    {
        return route('payment.simulate', ['orderNumber' => $order->order_number]);
    }
}
