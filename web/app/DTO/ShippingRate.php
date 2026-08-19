<?php

namespace App\DTO;

readonly class ShippingRate
{
    public function __construct(
        public string $courier,
        public string $service,
        public string $name,
        public int $cost,
        public string $etd,
        public string $description = '',
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->courier.':'.$this->service,
            'courier' => $this->courier,
            'service' => $this->service,
            'name' => $this->name,
            'cost' => $this->cost,
            'cost_fmt' => 'Rp '.number_format($this->cost, 0, ',', '.'),
            'etd' => $this->etd,
            'description' => $this->description,
        ];
    }
}
