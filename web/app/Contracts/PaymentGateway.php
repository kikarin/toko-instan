<?php

namespace App\Contracts;

use App\DTO\PaymentInitResult;
use App\DTO\WebhookPaymentResult;
use App\Enums\PaymentMethod;
use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function createPayment(Order $order, PaymentMethod $method): PaymentInitResult;

    public function verifyWebhook(Request $request): bool;

    public function parseWebhook(Request $request): WebhookPaymentResult;
}
