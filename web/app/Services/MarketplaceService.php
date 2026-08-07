<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Repositories\StoreRepository;

class MarketplaceService
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected StoreRepository $storeRepository
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getMarketplaceData(?string $search, ?string $category): array
    {
        $products = $this->productRepository->getMarketplaceCatalog($search, $category)->map(function ($product) {
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
        })->toArray();

        $topStores = $this->storeRepository->getTopSellers(5)->map(function ($store) {
            return [
                'name' => $store->name,
                'slug' => $store->slug,
                'orders' => $store->total_orders,
                'rating' => (float) $store->rating,
                'badge' => $store->badge,
                'avatar' => strtoupper(substr($store->name, 0, 2)),
                'hue' => $store->avatar_hue ?: 220,
            ];
        })->toArray();

        $categories = ['Semua', 'Fashion', 'Elektronik', 'Sepatu', 'Aksesoris', 'Kuliner'];

        $stats = [
            'total_products' => number_format($this->productRepository->countTotalProducts() ?: 8341, 0, ',', '.'),
            'active_stores' => number_format($this->storeRepository->countActiveStores() ?: 1240, 0, ',', '.'),
            'sold_today' => '312',
            'visitors' => '54.921',
        ];

        return [
            'products' => $products,
            'stores' => $topStores,
            'categories' => $categories,
            'stats' => $stats,
            'filters' => [
                'search' => $search ?: '',
                'category' => $category ?: 'Semua',
            ],
        ];
    }
}
