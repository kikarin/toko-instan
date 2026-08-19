<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Collection;
use RuntimeException;

class ReviewService
{
    /**
     * @return Collection<int, Review>
     */
    public function forProduct(Product $product): Collection
    {
        return Review::query()
            ->with('user:id,name')
            ->where('product_id', $product->id)
            ->orderByDesc('id')
            ->get();
    }

    public function eligibleItem(User $user, Product $product): ?OrderItem
    {
        return OrderItem::query()
            ->where('product_id', $product->id)
            ->whereDoesntHave('review')
            ->whereHas('order', function ($query) use ($user) {
                $query->where('customer_email', $user->email)
                    ->whereIn('status', ['paid', 'processing', 'packed', 'shipped', 'completed']);
            })
            ->latest('id')
            ->first();
    }

    public function create(User $user, Product $product, int $rating, ?string $body, ?string $photoPath, ?int $orderItemId = null): Review
    {
        $item = $orderItemId
            ? OrderItem::query()->with('order')->find($orderItemId)
            : $this->eligibleItem($user, $product);

        if (! $item || (int) $item->product_id !== (int) $product->id) {
            throw new RuntimeException('Kamu hanya bisa review produk yang sudah dibeli.');
        }

        if ($item->order->customer_email !== $user->email) {
            throw new RuntimeException('Pesanan ini bukan milikmu.');
        }

        if (! in_array($item->order->status, ['paid', 'processing', 'packed', 'shipped', 'completed'], true)) {
            throw new RuntimeException('Pesanan belum bisa diulas.');
        }

        if (Review::query()->where('order_item_id', $item->id)->exists()) {
            throw new RuntimeException('Item ini sudah diulas.');
        }

        $review = Review::query()->create([
            'tenant_id' => $item->tenant_id,
            'store_id' => $item->order->store_id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $item->order_id,
            'order_item_id' => $item->id,
            'rating' => max(1, min(5, $rating)),
            'body' => $body,
            'photo_path' => $photoPath,
        ]);

        $this->syncProductRating($product);

        return $review;
    }

    public function syncProductRating(Product $product): void
    {
        $avg = Review::query()->where('product_id', $product->id)->avg('rating');
        $product->update(['rating' => $avg ? round((float) $avg, 1) : 0]);
    }
}
