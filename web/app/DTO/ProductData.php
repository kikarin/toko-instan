<?php

namespace App\DTO;

use App\Enums\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
        public string $type = ProductType::Physical->value,
        public ?string $digitalFilePath = null,
        public ?string $digitalFileName = null,
        public ?string $digitalFileMime = null,
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
            'type' => ['nullable', 'string', Rule::enum(ProductType::class)],
            'digital_file_path' => ['nullable', 'string', 'max:2048'],
            'digital_file_name' => ['nullable', 'string', 'max:255'],
            'digital_file_mime' => ['nullable', 'string', 'max:120'],
            'variant_options' => ['nullable', 'array'],
            'variant_options.*.name' => ['required', 'string', 'max:255'],
            'variant_options.*.values' => ['required', 'array'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.stock' => ['required_with:variants', 'integer', 'min:0'],
        ])->after(function ($validator) use ($request) {
            $type = $request->input('type', ProductType::Physical->value);
            if ($type === ProductType::Digital->value && blank($request->input('digital_file_path'))) {
                $validator->errors()->add('digital_file_path', 'File digital wajib diunggah untuk produk digital.');
            }
        })->validate();

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
            type: $validated['type'] ?? ProductType::Physical->value,
            digitalFilePath: $validated['digital_file_path'] ?? null,
            digitalFileName: $validated['digital_file_name'] ?? null,
            digitalFileMime: $validated['digital_file_mime'] ?? null,
        );
    }
}
