<?php

namespace App\Repositories;

use App\DTO\ProductData;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class ProductRepository
{
    /**
     * @return Collection<int, Product>
     */
    public function getMarketplaceCatalog(?string $search = null, ?string $category = null, ?int $storeId = null): Collection
    {
        $query = Product::with('store')->where('is_active', true);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        if ($search) {
            $searchLower = mb_strtolower(trim($search));
            $words = array_filter(explode(' ', $searchLower));

            $query->where(function ($q) use ($searchLower, $words) {
                // Exact full search phrase match on name, category, tag, or store name
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(category) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(tag) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereHas('store', function ($sq) use ($searchLower) {
                        $sq->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"]);
                    });

                // Tokenized individual word matches
                foreach ($words as $word) {
                    $wordLower = mb_strtolower($word);
                    $q->orWhereRaw('LOWER(name) LIKE ?', ["%{$wordLower}%"])
                        ->orWhereRaw('LOWER(category) LIKE ?', ["%{$wordLower}%"])
                        ->orWhereRaw('LOWER(tag) LIKE ?', ["%{$wordLower}%"])
                        ->orWhereHas('store', function ($sq) use ($wordLower) {
                            $sq->whereRaw('LOWER(name) LIKE ?', ["%{$wordLower}%"]);
                        });
                }
            });
        }

        if ($category && $category !== 'Semua') {
            $query->where('category', $category);
        }

        return $query->get();
    }

    public function countTotalProducts(): int
    {
        return Product::count();
    }

    public function find(int $id): ?Product
    {
        return Product::find($id);
    }

    /**
     * @return Collection<int, Product>
     */
    public function getForStore(int $storeId): Collection
    {
        return Product::where('store_id', $storeId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getActiveProductsForStore(int $storeId): Collection
    {
        return Product::where('store_id', $storeId)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getActiveStoreProducts(int $storeId, ?string $category = null): Collection
    {
        $query = Product::where('store_id', $storeId)->where('is_active', true);

        if ($category && $category !== 'Semua') {
            $query->where('category', $category);
        }

        return $query->get();
    }

    public function getActiveStoreCategories(int $storeId): array
    {
        return Product::where('store_id', $storeId)
            ->where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values()
            ->toArray();
    }

    public function countActiveStoreProducts(int $storeId): int
    {
        return Product::where('store_id', $storeId)->where('is_active', true)->count();
    }

    public function findActiveProductBySlug(int $storeId, string $slug): ?Product
    {
        return Product::where('store_id', $storeId)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['variants'])
            ->first();
    }

    public function getForStorePaginated(int $storeId, int $perPage = 10)
    {
        return Product::where('store_id', $storeId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getInventoryStats(int $storeId, int $lowStockThreshold): array
    {
        return [
            'total' => Product::where('store_id', $storeId)->count(),
            'low_stock' => Product::where('store_id', $storeId)->where('stock', '<=', $lowStockThreshold)->count(),
            'out_of_stock' => Product::where('store_id', $storeId)->where('stock', 0)->count(),
        ];
    }

    public function findForStore(int $id, int $storeId): ?Product
    {
        return Product::where('id', $id)->where('store_id', $storeId)->first();
    }

    public function createForStore(int $storeId, ProductData $data): Product
    {
        $attributes = [
            'store_id' => $storeId,
            'name' => $data->name,
            'slug' => \Str::slug($data->name).'-'.random_int(1000, 9999),
            'category' => $data->category,
            'price' => $data->price,
            'sold' => 0,
            'rating' => 4.8,
            'tag' => $data->tag,
            'img' => $data->img,
            'stock' => $data->stock,
            'is_active' => $data->isActive,
            'description' => $data->description,
            'type' => $data->type,
            'digital_file_path' => $data->digitalFilePath,
            'digital_file_name' => $data->digitalFileName,
            'digital_file_mime' => $data->digitalFileMime,
            'sku' => $data->sku ?: ('NK-'.strtoupper(\Str::random(6))),
            'brand' => $data->brand ?: 'Nike',
            'weight_gram' => $data->weightGram ?: 500,
            'variant_options' => $data->variantOptions,
            'meta_title' => $data->metaTitle,
            'meta_description' => $data->metaDescription,
            'seo_tags' => $data->seoTags,
            'marketing_caption' => $data->marketingCaption,
        ];

        $filtered = array_filter($attributes, function ($val, $key) {
            return Schema::hasColumn('products', $key);
        }, ARRAY_FILTER_USE_BOTH);

        $product = Product::create($filtered);

        if ($data->variants !== null) {
            $this->syncVariants($product, $data->variants);
        }

        return $product;
    }

    public function updateProduct(Product $product, ProductData $data): void
    {
        $attributes = [
            'name' => $data->name,
            'category' => $data->category,
            'price' => $data->price,
            'stock' => $data->stock,
            'tag' => $data->tag,
            'img' => $data->img,
            'is_active' => $data->isActive,
            'description' => $data->description,
            'type' => $data->type,
            'digital_file_path' => $data->digitalFilePath,
            'digital_file_name' => $data->digitalFileName,
            'digital_file_mime' => $data->digitalFileMime,
            'sku' => $data->sku,
            'brand' => $data->brand,
            'weight_gram' => $data->weightGram,
            'variant_options' => $data->variantOptions,
            'meta_title' => $data->metaTitle,
            'meta_description' => $data->metaDescription,
            'seo_tags' => $data->seoTags,
            'marketing_caption' => $data->marketingCaption,
        ];

        $filtered = array_filter($attributes, function ($val, $key) {
            return Schema::hasColumn('products', $key);
        }, ARRAY_FILTER_USE_BOTH);

        $product->update($filtered);

        if ($data->variants !== null) {
            $this->syncVariants($product, $data->variants);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $variants
     */
    protected function syncVariants(Product $product, array $variants): void
    {
        $existingVariantIds = $product->variants()->pluck('id')->toArray();
        $updatedVariantIds = [];

        foreach ($variants as $variantData) {
            if (! empty($variantData['id']) && in_array($variantData['id'], $existingVariantIds)) {
                // Update existing
                $product->variants()->where('id', $variantData['id'])->update([
                    'name' => $variantData['name'],
                    'price' => $variantData['price'] ?? null,
                    'stock' => $variantData['stock'] ?? 0,
                    'sku' => $variantData['sku'] ?? null,
                    'img' => $variantData['img'] ?? null,
                ]);
                $updatedVariantIds[] = $variantData['id'];
            } else {
                // Create new
                $newVariant = $product->variants()->create([
                    'name' => $variantData['name'],
                    'price' => $variantData['price'] ?? null,
                    'stock' => $variantData['stock'] ?? 0,
                    'sku' => $variantData['sku'] ?? null,
                    'img' => $variantData['img'] ?? null,
                    'is_active' => true,
                ]);
                $updatedVariantIds[] = $newVariant->id;
            }
        }

        // Delete variants that were not in the updated list
        $variantsToDelete = array_diff($existingVariantIds, $updatedVariantIds);
        if (! empty($variantsToDelete)) {
            $product->variants()->whereIn('id', $variantsToDelete)->delete();
        }
    }

    public function updateStock(Product $product, int $stock): void
    {
        $product->update(['stock' => max(0, $stock)]);
    }

    public function toggleActive(Product $product): bool
    {
        $product->update(['is_active' => ! $product->is_active]);

        return (bool) $product->is_active;
    }

    public function deleteProduct(Product $product): void
    {
        $product->delete();
    }

    public function decrementStock(int $id, int $qty): void
    {
        $product = Product::find($id);
        if ($product) {
            $product->decrement('stock', $qty);
            $product->increment('sold', $qty);
        }
    }
}
