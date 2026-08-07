<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class WishlistService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listFor(User $user): Collection
    {
        return $user->wishlistProducts()
            ->with('store')
            ->get()
            ->map(fn (Product $product) => $this->format($product))
            ->values();
    }

    public function toggle(User $user, int $productId): bool
    {
        $product = Product::findOrFail($productId);

        $exists = $user->wishlistProducts()
            ->wherePivot('product_id', $productId)
            ->exists();

        if ($exists) {
            $user->wishlistProducts()->detach($productId);

            return false;
        }

        $user->wishlistProducts()->attach($productId);

        return true;
    }

    public function remove(User $user, int $productId): void
    {
        $user->wishlistProducts()->detach($productId);
    }

    /**
     * @return array<string, mixed>
     */
    private function format(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => 'Rp '.number_format($product->price, 0, ',', '.'),
            'priceNum' => (int) $product->price,
            'sold' => $product->sold,
            'rating' => (float) $product->rating,
            'store' => $product->store ? $product->store->name : 'Official Store',
            'storeSlug' => $product->store?->slug,
            'img' => $product->img,
            'tag' => $product->tag,
            'cat' => $product->category,
        ];
    }
}
