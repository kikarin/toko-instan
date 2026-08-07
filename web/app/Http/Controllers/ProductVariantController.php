<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductVariantController extends Controller
{
    public function index(Request $request, int $productId): Response
    {
        $product = $this->findProduct($request, $productId);

        return Inertia::render('Products/Variants', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
            ],
            'variants' => $product->variants()
                ->orderBy('name')
                ->get()
                ->map(fn (ProductVariant $v) => $this->format($v))
                ->values(),
        ]);
    }

    public function store(Request $request, int $productId): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'sku' => 'nullable|string|max:120',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
        ]);

        $this->findProduct($request, $productId)->variants()->create([
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? null,
            'price' => $validated['price'] ?? null,
            'stock' => (int) ($validated['stock'] ?? 0),
            'is_active' => true,
        ]);

        return back()->with('success', 'Varian produk ditambahkan.');
    }

    public function update(Request $request, int $productId, int $variantId): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'sku' => 'nullable|string|max:120',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $variant = $this->findVariant($request, $productId, $variantId);

        $variant->update([
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? null,
            'price' => $validated['price'] ?? null,
            'stock' => (int) ($validated['stock'] ?? 0),
            'is_active' => (bool) ($validated['is_active'] ?? $variant->is_active),
        ]);

        return back()->with('success', 'Varian produk diperbarui.');
    }

    public function destroy(Request $request, int $productId, int $variantId): RedirectResponse
    {
        $this->findVariant($request, $productId, $variantId)->delete();

        return back()->with('success', 'Varian produk dihapus.');
    }

    private function findProduct(Request $request, int $productId): Product
    {
        $store = $request->user()->store;

        abort_unless($store !== null, 404);

        return Product::where('store_id', $store->id)->findOrFail($productId);
    }

    private function findVariant(Request $request, int $productId, int $variantId): ProductVariant
    {
        return $this->findProduct($request, $productId)
            ->variants()
            ->findOrFail($variantId);
    }

    /**
     * @return array<string, mixed>
     */
    private function format(ProductVariant $variant): array
    {
        return [
            'id' => $variant->id,
            'name' => $variant->name,
            'sku' => $variant->sku,
            'price' => $variant->price === null ? null : (float) $variant->price,
            'formatted_price' => $variant->price === null ? null : 'Rp '.number_format((float) $variant->price, 0, ',', '.'),
            'stock' => $variant->stock,
            'is_active' => (bool) $variant->is_active,
        ];
    }
}
