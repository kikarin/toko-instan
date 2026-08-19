<?php

namespace App\DTO;

readonly class WebhookPaymentResult
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $orderNumber,
        public string $externalId,
        public string $idempotencyKey,
        public bool $isPaid,
        public string $gatewayStatus,
        public array $payload = [],
    ) {}
}
