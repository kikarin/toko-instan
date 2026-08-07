<?php

namespace App\Repositories;

use App\DTO\ProductData;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    /**
     * @return Collection<int, Product>
     */
    public function getMarketplaceCatalog(?string $search = null, ?string $category = null): Collection
    {
        $query = Product::with('store')->where('is_active', true);

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

    public function findForStore(int $id, int $storeId): ?Product
    {
        return Product::where('id', $id)->where('store_id', $storeId)->first();
    }

    public function createForStore(int $storeId, ProductData $data): Product
    {
        return Product::create([
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
        ]);
    }

    public function updateProduct(Product $product, ProductData $data): void
    {
        $product->update([
            'name' => $data->name,
            'category' => $data->category,
            'price' => $data->price,
            'stock' => $data->stock,
            'tag' => $data->tag,
            'img' => $data->img,
            'is_active' => $data->isActive,
        ]);
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
