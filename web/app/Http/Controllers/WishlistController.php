<?php

namespace App\Http\Controllers;

use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    public function __construct(
        protected WishlistService $wishlistService
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Wishlist', [
            'products' => $this->wishlistService->listFor($request->user()),
        ]);
    }

    public function toggle(Request $request, int $productId): JsonResponse
    {
        $added = $this->wishlistService->toggle($request->user(), $productId);

        return response()->json([
            'added' => $added,
            'count' => $request->user()->wishlistProducts()->count(),
        ]);
    }

    public function destroy(Request $request, int $productId): RedirectResponse
    {
        $this->wishlistService->remove($request->user(), $productId);

        return back()->with('success', 'Produk dihapus dari wishlist.');
    }
}
