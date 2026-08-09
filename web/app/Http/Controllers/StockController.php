<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class StockController extends Controller
{
    public function __construct(
        protected ProductService $productService,
        protected StockService $stockService
    ) {}

    public function index(Request $request): Response
    {
        $products = $this->productService->listForSeller($request->user()->id)
            ->map(fn (Product $product) => [
                ...$this->productService->format($product),
                'low_stock' => $product->stock <= $this->lowStockThreshold(),
            ])
            ->values()
            ->toArray();

        return Inertia::render('Inventory/Index', [
            'products' => $products,
            'lowStockThreshold' => $this->lowStockThreshold(),
            'totalProducts' => count($products),
            'lowStockCount' => collect($products)->where('low_stock', true)->count(),
            'outOfStockCount' => collect($products)->where('stock', 0)->count(),
        ]);
    }

    public function history(Request $request, int $id): Response
    {
        $product = $this->productService->findForSeller($id, $request->user()->id);

        if (! $product) {
            abort(404, 'Produk tidak ditemukan');
        }

        return Inertia::render('Inventory/History', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'img' => $product->img,
                'stock' => $product->stock,
                'sku' => $product->sku,
            ],
            'movements' => $this->stockService->history($product),
        ]);
    }

    public function storeIn(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        $product = $this->productService->findForSeller($id, $request->user()->id);

        if (! $product) {
            abort(404, 'Produk tidak ditemukan');
        }

        $this->stockService->stockIn($product, $validated['quantity'], $validated['reason'] ?? null, $request->user());

        return back()->with('success', 'Stok masuk dicatat.');
    }

    public function storeOut(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        $product = $this->productService->findForSeller($id, $request->user()->id);

        if (! $product) {
            abort(404, 'Produk tidak ditemukan');
        }

        try {
            $this->stockService->stockOut($product, $validated['quantity'], $validated['reason'] ?? null, $request->user());
        } catch (RuntimeException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()]);
        }

        return back()->with('success', 'Stok keluar dicatat.');
    }

    public function adjust(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'new_stock' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $product = $this->productService->findForSeller($id, $request->user()->id);

        if (! $product) {
            abort(404, 'Produk tidak ditemukan');
        }

        try {
            $this->stockService->adjust($product, $validated['new_stock'], $validated['reason'] ?? null, $request->user());
        } catch (RuntimeException $e) {
            return back()->withErrors(['new_stock' => $e->getMessage()]);
        }

        return back()->with('success', 'Stok disesuaikan.');
    }

    private function lowStockThreshold(): int
    {
        return 10;
    }
}
