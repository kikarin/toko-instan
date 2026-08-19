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

class CodPaymentGateway implements PaymentGateway
{
    public function createPayment(Order $order, PaymentMethod $method): PaymentInitResult
    {
        return new PaymentInitResult(
            provider: PaymentProvider::Cod->value,
            method: PaymentMethod::Cod->value,
            status: PaymentStatus::Pending->value,
            meta: [
                'instructions' => 'Bayar tunai ke kurir saat barang diterima. Seller akan konfirmasi setelah COD diterima.',
            ],
        );
    }

    public function verifyWebhook(Request $request): bool
    {
        return false;
    }

    public function parseWebhook(Request $request): WebhookPaymentResult
    {
        throw new LogicException('COD tidak memakai webhook gateway.');
    }
}
