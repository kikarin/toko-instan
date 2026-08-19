<?php

namespace App\DTO;

readonly class PaymentInitResult
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $provider,
        public string $method,
        public string $status,
        public ?string $snapToken = null,
        public ?string $redirectUrl = null,
        public ?string $externalId = null,
        public array $meta = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'method' => $this->method,
            'status' => $this->status,
            'snap_token' => $this->snapToken,
            'redirect_url' => $this->redirectUrl,
            'external_id' => $this->externalId,
            'meta' => $this->meta,
        ];
    }
}
