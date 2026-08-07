<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __construct(
        protected ProductRepository $productRepository
    ) {}

    public function show(Request $request): Response
    {
        $user = $request->user();
        $email = $user?->email;

        // Sample recommended products
        $recommendedProducts = $this->productRepository->getMarketplaceCatalog(null, null)
            ->take(6)
            ->map(function ($product) {
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
                    'discount' => rand(10, 30),
                ];
            })->toArray();

        // Transaction stats counts
        $orderCounts = [
            'bayar' => $email ? Order::where('customer_email', $email)->where('status', 'pending')->count() : 0,
            'diproses' => $email ? Order::where('customer_email', $email)->where('status', 'paid')->count() : 0,
            'dikirim' => $email ? Order::where('customer_email', $email)->where('status', 'shipped')->count() : 0,
            'sudah_tiba' => $email ? Order::where('customer_email', $email)->where('status', 'completed')->count() : 0,
            'ulasan' => 0,
        ];

        return Inertia::render('Account', [
            'user' => [
                'id' => $user?->id,
                'name' => $user?->name ?? 'User Toko Instan',
                'email' => $user?->email,
                'role' => $user?->role ?? 'buyer',
                'avatar' => $user?->avatar,
                'created_at' => $user?->created_at?->format('M Y'),
            ],
            'orderCounts' => $orderCounts,
            'vouchers' => [
                'shopping' => 5,
                'shipping' => 6,
            ],
            'recommendedProducts' => $recommendedProducts,
        ]);
    }
}
