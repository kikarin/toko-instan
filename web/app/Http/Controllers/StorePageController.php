<?php

namespace App\Http\Controllers;

use App\Repositories\ProductRepository;
use App\Repositories\StoreRepository;
use App\Services\StoreCmsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class StorePageController extends Controller
{
    public function __construct(
        protected StoreRepository $storeRepository,
        protected ProductRepository $productRepository,
        protected StoreCmsService $cmsService
    ) {}

    public function show(Request $request, string $slug): Response|HttpResponse
    {
        $store = $this->storeRepository->findBySlug($slug);

        if (! $store) {
            abort(404);
        }

        if (! $store->is_active) {
            return $this->closedStoreResponse($store);
        }

        $category = $request->query('category');

        $products = $this->productRepository->getActiveStoreProducts($store->id, $category)
            ->map(function ($product) use ($store) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => 'Rp '.number_format($product->price, 0, ',', '.'),
                'priceNum' => (int) $product->price,
                'sold' => $product->sold,
                'rating' => (float) $product->rating,
                'store' => $store->name,
                'storeSlug' => $store->slug,
                'img' => $product->img,
                'tag' => $product->tag,
                'cat' => $product->category,
                'description' => $product->description,
                'sku' => $product->sku,
                'brand' => $product->brand,
                'weightGram' => $product->weight_gram,
                'stock' => $product->stock,
                'variant_options' => $product->variant_options,
                'variants' => $product->variants,
            ];
        })->toArray();

        // Get distinct categories from this store's products
        $categories = ['Semua', ...$this->productRepository->getActiveStoreCategories($store->id)];

        $showcase = $this->cmsService->normalize($store->showcase);

        $featuredIds = array_values(array_filter(
            (array) ($showcase['featured_product_ids'] ?? []),
            fn ($id) => is_int($id) || (is_string($id) && ctype_digit($id)),
        ));

        $featured = array_values(array_filter(array_map(
            fn ($id) => collect($products)->firstWhere('id', (int) $id),
            $featuredIds,
        )));

        return Inertia::render('StorePage', [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
                'description' => $store->description ?? 'Toko terpercaya di Toko Instan. Selalu menyediakan produk berkualitas dengan harga terjangkau.',
                'logo' => $store->logo,
                'category' => $store->category,
                'rating' => (float) $store->rating,
                'totalOrders' => $store->total_orders,
                'totalProducts' => $this->productRepository->countActiveStoreProducts($store->id),
                'badge' => $store->badge,
                'avatar' => strtoupper(substr($store->name, 0, 2)),
                'avatarHue' => $store->avatar_hue ?: 220,
                'memberSince' => $store->created_at?->format('M Y'),
                'gmv' => 'Rp '.number_format($store->gmv, 0, ',', '.'),
                'banner_url' => $store->banner_url,
                'banner_urls' => $store->banner_urls ?? [],
                'highlights' => $store->highlights ?? [],
                'hero_config' => $store->hero_config ?? null,
                'headline' => $store->headline,
            ],
            'theme' => $this->cmsService->resolve($store),
            'showcase' => $showcase,
            'featured' => $featured,
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'category' => $category ?: 'Semua',
            ],
        ]);
    }

    public function product(Request $request, string $slug, string $productSlug): Response|HttpResponse
    {
        $store = $this->storeRepository->findBySlug($slug);

        if (! $store) {
            abort(404);
        }

        $product = $this->productRepository->findActiveProductBySlug($store->id, $productSlug);

        if (! $product) {
            abort(404);
        }

        return Inertia::render('ProductDetail', [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
                'logo' => $store->logo,
                'badge' => $store->badge,
                'avatar' => strtoupper(substr($store->name, 0, 2)),
                'avatarHue' => $store->avatar_hue ?: 220,
            ],
            'theme' => $this->cmsService->resolve($store),
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => 'Rp '.number_format($product->price, 0, ',', '.'),
                'priceNum' => (int) $product->price,
                'description' => $product->description,
                'img' => $product->img,
                'rating' => (float) $product->rating,
                'sold' => $product->sold,
                'category' => $product->category,
                'stock' => $product->stock,
                'sku' => $product->sku,
                'brand' => $product->brand,
                'weightGram' => $product->weight_gram,
                'variant_options' => $product->variant_options,
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'sku' => $variant->sku,
                        'price' => 'Rp '.number_format($variant->price, 0, ',', '.'),
                        'priceNum' => (int) $variant->price,
                        'stock' => $variant->stock,
                        'img' => $variant->img,
                    ];
                })->toArray(),
            ],
        ]);
    }

    protected function closedStoreResponse($store): Response
    {
        return Inertia::render('StoreClosed', [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
                'logo' => $store->logo,
            ],
        ]);
    }
}
