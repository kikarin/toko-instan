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
        );
    }
}
