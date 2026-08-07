<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Label;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = $this->tenantId($request);

        $categories = Category::where('tenant_id', $tenantId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $c) => $this->formatCategory($c))
            ->values();

        $brands = Brand::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(fn (Brand $b) => $this->formatBrand($b))
            ->values();

        $labels = Label::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(fn (Label $l) => $this->formatLabel($l))
            ->values();

        return Inertia::render('Catalog/Index', [
            'categories' => $categories,
            'brands' => $brands,
            'labels' => $labels,
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
        ]);

        Category::create([
            'tenant_id' => $this->tenantId($request),
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::random(4),
            'sort_order' => Category::where('tenant_id', $this->tenantId($request))->count(),
        ]);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
        ]);

        $category = $this->findCategory($request, $id);
        $category->update(['name' => $validated['name']]);

        return back()->with('success', 'Kategori diperbarui.');
    }

    public function destroyCategory(Request $request, int $id): RedirectResponse
    {
        $this->findCategory($request, $id)->delete();

        return back()->with('success', 'Kategori dihapus.');
    }

    public function storeBrand(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
        ]);

        Brand::create([
            'tenant_id' => $this->tenantId($request),
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::random(4),
        ]);

        return back()->with('success', 'Brand berhasil ditambahkan.');
    }

    public function updateBrand(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
        ]);

        $this->findBrand($request, $id)->update(['name' => $validated['name']]);

        return back()->with('success', 'Brand diperbarui.');
    }

    public function destroyBrand(Request $request, int $id): RedirectResponse
    {
        $this->findBrand($request, $id)->delete();

        return back()->with('success', 'Brand dihapus.');
    }

    public function storeLabel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'color' => 'nullable|string|max:30',
        ]);

        Label::create([
            'tenant_id' => $this->tenantId($request),
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::random(4),
            'color' => $validated['color'] ?? null,
        ]);

        return back()->with('success', 'Label berhasil ditambahkan.');
    }

    public function updateLabel(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'color' => 'nullable|string|max:30',
        ]);

        $this->findLabel($request, $id)->update($validated);

        return back()->with('success', 'Label diperbarui.');
    }

    public function destroyLabel(Request $request, int $id): RedirectResponse
    {
        $this->findLabel($request, $id)->delete();

        return back()->with('success', 'Label dihapus.');
    }

    private function tenantId(Request $request): ?int
    {
        return $request->user()->primaryTenant?->id;
    }

    private function findCategory(Request $request, int $id): Category
    {
        return Category::where('tenant_id', $this->tenantId($request))->findOrFail($id);
    }

    private function findBrand(Request $request, int $id): Brand
    {
        return Brand::where('tenant_id', $this->tenantId($request))->findOrFail($id);
    }

    private function findLabel(Request $request, int $id): Label
    {
        return Label::where('tenant_id', $this->tenantId($request))->findOrFail($id);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatCategory(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatBrand(Brand $brand): array
    {
        return [
            'id' => $brand->id,
            'name' => $brand->name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatLabel(Label $label): array
    {
        return [
            'id' => $label->id,
            'name' => $label->name,
            'color' => $label->color,
        ];
    }
}
