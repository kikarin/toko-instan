<?php

namespace App\DTO;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'tag' => ['nullable', 'string', 'max:50'],
            'img' => ['nullable', 'string', 'url', 'max:2048'],
            'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'weight_gram' => ['nullable', 'integer', 'min:1'],
            'variant_options' => ['nullable', 'array'],
            'variant_options.*.name' => ['required', 'string', 'max:255'],
            'variant_options.*.values' => ['required', 'array'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.stock' => ['required_with:variants', 'integer', 'min:0'],
        ])->validate();

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
