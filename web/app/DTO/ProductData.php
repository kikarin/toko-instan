<?php

namespace App\DTO;

class ProductData
{
    public function __construct(
        public string $name,
        public string $category,
        public int $price,
        public int $stock,
        public bool $isActive = true,
        public ?string $tag = null,
        public ?string $img = null,
        public ?string $description = null,
        public ?string $sku = null,
        public ?string $brand = 'Nike',
        public int $weightGram = 500,
        public ?array $variantOptions = null,
        public ?array $variants = null,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromRequest(array $validated): self
    {
        return new self(
            name: $validated['name'],
            category: $validated['category'],
            price: (int) $validated['price'],
            stock: (int) ($validated['stock'] ?? 0),
            isActive: (bool) ($validated['is_active'] ?? true),
            tag: $validated['tag'] ?? null,
            img: $validated['img'] ?? null,
            description: $validated['description'] ?? null,
            sku: $validated['sku'] ?? null,
            brand: $validated['brand'] ?? 'Nike',
            weightGram: (int) ($validated['weight_gram'] ?? 500),
            variantOptions: $validated['variant_options'] ?? null,
            variants: $validated['variants'] ?? null,
        );
    }
}
