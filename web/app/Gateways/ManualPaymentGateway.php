<?php

namespace App\Gateways;

use App\Contracts\PaymentGateway;
use App\DTO\PaymentInitResult;
use App\DTO\WebhookPaymentResult;
use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Http\Request;
use LogicException;

class ManualPaymentGateway implements PaymentGateway
{
    public function createPayment(Order $order, PaymentMethod $method): PaymentInitResult
    {
        return new PaymentInitResult(
            provider: PaymentProvider::Manual->value,
            method: PaymentMethod::Transfer->value,
            status: PaymentStatus::Pending->value,
            meta: [
                'instructions' => 'Transfer ke rekening toko lalu upload bukti pembayaran.',
            ],
        );
    }

    public function verifyWebhook(Request $request): bool
    {
        return false;
    }

    public function parseWebhook(Request $request): WebhookPaymentResult
    {
        throw new LogicException('Manual transfer tidak memakai webhook gateway.');
    }
}
