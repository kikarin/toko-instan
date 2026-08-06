<?php

namespace App\Http\Controllers;

use App\DTO\ProductData;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(): Response
    {
        $products = $this->productService->listForSeller()
            ->map(fn ($product) => $this->productService->format($product))
            ->values()
            ->toArray();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $this->productService->categories(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Products/Form', [
            'categories' => $this->productService->categories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validate($request);
        $this->productService->create(ProductData::fromRequest($validated));

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Request $request, int $id): Response
    {
        $product = $this->productService->findForSeller($id);

        if (! $product) {
            abort(404, 'Produk tidak ditemukan');
        }

        return Inertia::render('Products/Form', [
            'product' => $this->productService->format($product),
            'categories' => $this->productService->categories(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $product = $this->productService->findForSeller($id);

        if (! $product) {
            abort(404, 'Produk tidak ditemukan');
        }

        $validated = $this->validate($request);
        $this->productService->update($product, ProductData::fromRequest($validated));

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $product = $this->productService->findForSeller($id);

        if (! $product) {
            abort(404, 'Produk tidak ditemukan');
        }

        $this->productService->delete($product);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    public function updateStock(Request $request, int $id): RedirectResponse
    {
        $product = $this->productService->findForSeller($id);

        if (! $product) {
            abort(404, 'Produk tidak ditemukan');
        }

        $validated = $request->validate([
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $this->productService->updateStock($product, (int) $validated['stock']);

        return redirect()->route('products.index')
            ->with('success', "Stok produk diperbarui menjadi {$validated['stock']}.");
    }

    public function toggleActive(Request $request, int $id): RedirectResponse
    {
        $product = $this->productService->findForSeller($id);

        if (! $product) {
            abort(404, 'Produk tidak ditemukan');
        }

        $active = $this->productService->toggleActive($product);

        return redirect()->route('products.index')
            ->with('success', $active
                ? 'Produk diaktifkan kembali.'
                : 'Produk dinonaktifkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validate(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'tag' => ['nullable', 'string', 'max:50'],
            'img' => ['nullable', 'string', 'url', 'max:2048'],
        ]);
    }
}
