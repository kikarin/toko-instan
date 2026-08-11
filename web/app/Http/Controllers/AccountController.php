<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Services\ProductService;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __construct(
        protected ProductService $productService,
        protected StoreService $storeService,
        protected OrderService $orderService
    ) {}

    public function show(string $storeSlug, Request $request): Response
    {
        $user = $request->user();
        $email = $user?->email;

        $activeStore = $this->storeService->getActiveStore($user?->id);

        // Sample recommended products
        $recommendedProducts = $this->productService->getRecommendedProducts($activeStore?->id, 6);

        // Transaction stats counts
        $orderCounts = $this->orderService->getCustomerOrderCounts($email);

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
