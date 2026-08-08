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

        $category = $request->query('category');

        $productsQuery = $store->products()->where('is_active', true);
        if ($category && $category !== 'Semua') {
            $productsQuery->where('category', $category);
        }

        $products = $productsQuery->get()->map(function ($product) use ($store) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => 'Rp '.number_format($product->price, 0, ',', '.'),
                'priceNum' => (int) $product->price,
                'sold' => $product->sold,
                'rating' => (float) $product->rating,
                'store' => $store->name,
                'storeSlug' => $store->slug,
                'img' => $product->img,
                'tag' => $product->tag,
                'cat' => $product->category,
            ];
        })->toArray();

        // Get distinct categories from this store's products
        $categories = ['Semua', ...$store->products()
            ->where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values()
            ->toArray()];

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
                'totalProducts' => $store->products()->where('is_active', true)->count(),
                'badge' => $store->badge,
                'avatar' => strtoupper(substr($store->name, 0, 2)),
                'avatarHue' => $store->avatar_hue ?: 220,
                'memberSince' => $store->created_at?->format('M Y'),
                'gmv' => 'Rp '.number_format($store->gmv, 0, ',', '.'),
                'bannerUrl' => $store->banner_url,
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
}
