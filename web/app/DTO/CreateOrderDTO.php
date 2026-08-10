<?php

namespace App\DTO;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'store_id' => ['nullable', 'integer'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string'],
            'shipping_courier' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.name' => ['required', 'string'],
            'items.*.price' => ['required', 'numeric'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ])->validate();

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
