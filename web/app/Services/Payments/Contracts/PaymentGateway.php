<?php

namespace App\Services\Payments\Contracts;

use App\Models\Order;

interface PaymentGateway
{
    /**
     * Create a payment intent for an order and return the payment URL
     * the customer should be redirected to.
     */
    public function createPayment(Order $order): string;
}
