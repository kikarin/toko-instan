<?php

namespace App\DTO;

use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateOrderDTO
{
    /**
     * @param  array<int, array{id?: int, variant_id?: int, name?: string, price?: float|int, qty: int}>  $items
     */
    public function __construct(
        public string $customerName,
        public string $customerEmail,
        public string $customerPhone,
        public string $shippingAddress,
        public string $shippingCourier,
        public string $paymentMethod,
        public array $items,
        public ?string $notes = null,
        public ?string $destinationCity = null,
        public ?string $destinationPostalCode = null,
        public ?string $shippingRateId = null,
        public ?int $shippingCost = null,
        public ?string $shippingService = null,
        public ?string $voucherCode = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $items = collect($request->input('items', []))
            ->map(fn ($item) => [
                ...is_array($item) ? $item : [],
                'id' => (int) ($item['id'] ?? 0),
                'qty' => max(1, (int) ($item['qty'] ?? 1)),
                'variant_id' => isset($item['variant_id']) && (int) $item['variant_id'] > 0
                    ? (int) $item['variant_id']
                    : null,
            ])
            ->all();

        $postal = $request->input('destination_postal_code');
        $request->merge([
            'destination_city' => trim((string) $request->input('destination_city', '')),
            'destination_postal_code' => filled($postal) ? substr((string) $postal, 0, 10) : null,
            'shipping_cost' => $request->filled('shipping_cost')
                ? (int) round((float) $request->input('shipping_cost'))
                : null,
            'shipping_rate_id' => $request->filled('shipping_rate_id')
                ? (string) $request->input('shipping_rate_id')
                : null,
            'items' => $items,
        ]);

        $validated = Validator::make($request->all(), [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string'],
            'shipping_courier' => ['nullable', 'string'],
            'shipping_rate_id' => ['nullable', 'string', 'max:80'],
            'shipping_cost' => ['nullable', 'integer', 'min:0'],
            'shipping_service' => ['nullable', 'string', 'max:80'],
            'destination_city' => ['nullable', 'string', 'max:120'],
            'destination_postal_code' => ['nullable', 'string', 'max:10'],
            'payment_method' => ['nullable', 'string', Rule::enum(PaymentMethod::class)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.name' => ['nullable', 'string'],
            'items.*.price' => ['nullable', 'numeric'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'voucher_code' => ['nullable', 'string', 'max:40'],
        ])->validate();

        return new self(
            customerName: $validated['customer_name'],
            customerEmail: $validated['customer_email'],
            customerPhone: $validated['customer_phone'],
            shippingAddress: $validated['shipping_address'],
            shippingCourier: $validated['shipping_courier'] ?? 'JNE Reguler',
            paymentMethod: $validated['payment_method'] ?? 'qris',
            items: $validated['items'] ?? [],
            notes: $validated['notes'] ?? null,
            destinationCity: $validated['destination_city'] ?? null,
            destinationPostalCode: $validated['destination_postal_code'] ?? null,
            shippingRateId: $validated['shipping_rate_id'] ?? null,
            shippingCost: isset($validated['shipping_cost']) ? (int) $validated['shipping_cost'] : null,
            shippingService: $validated['shipping_service'] ?? null,
            voucherCode: isset($validated['voucher_code']) ? strtoupper(trim((string) $validated['voucher_code'])) : null,
        );
    }
}
