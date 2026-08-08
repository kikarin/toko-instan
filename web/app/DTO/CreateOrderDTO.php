<?php

namespace App\DTO;

class CreateOrderDTO
{
    /**
     * @param  array<int, array{id?: int, variant_id?: int, name: string, price: float|int, qty: int}>  $items
     */
    public function __construct(
        public int $storeId,
        public string $customerName,
        public string $customerEmail,
        public string $customerPhone,
        public string $shippingAddress,
        public string $shippingCourier,
        public string $paymentMethod,
        public array $items,
        public ?string $notes = null
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromRequest(array $validated): self
    {
        return new self(
            storeId: (int) ($validated['store_id'] ?? 1),
            customerName: $validated['customer_name'],
            customerEmail: $validated['customer_email'],
            customerPhone: $validated['customer_phone'],
            shippingAddress: $validated['shipping_address'],
            shippingCourier: $validated['shipping_courier'] ?? 'JNE Reguler',
            paymentMethod: $validated['payment_method'] ?? 'qris',
            items: $validated['items'] ?? [],
            notes: $validated['notes'] ?? null
        );
    }
}
