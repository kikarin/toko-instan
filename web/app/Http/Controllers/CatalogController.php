<?php

namespace App\Http\Controllers;

use App\DTO\Catalog\BrandDTO;
use App\DTO\Catalog\CategoryDTO;
use App\DTO\Catalog\LabelDTO;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Label;
use App\Services\CatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __construct(protected CatalogService $catalogService) {}

    public function index(Request $request): Response
    {
        $tenantId = $this->tenantId($request);

        $categories = $this->catalogService->getCategoriesByTenant($tenantId)
            ->map(fn (Category $c) => $this->formatCategory($c))
            ->values();

        $brands = $this->catalogService->getBrandsByTenant($tenantId)
            ->map(fn (Brand $b) => $this->formatBrand($b))
            ->values();

        $labels = $this->catalogService->getLabelsByTenant($tenantId)
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
        $dto = CategoryDTO::fromRequest($request);
        $this->catalogService->createCategory($dto, $this->tenantId($request));

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, int $id): RedirectResponse
    {
        $dto = CategoryDTO::fromRequest($request);
        $this->catalogService->updateCategory($id, $dto);

        return back()->with('success', 'Kategori diperbarui.');
    }

    public function destroyCategory(Request $request, int $id): RedirectResponse
    {
        $this->catalogService->deleteCategory($id);

        return back()->with('success', 'Kategori dihapus.');
    }

    public function storeBrand(Request $request): RedirectResponse
    {
        $dto = BrandDTO::fromRequest($request);
        $this->catalogService->createBrand($dto, $this->tenantId($request));

        return back()->with('success', 'Brand berhasil ditambahkan.');
    }

    public function updateBrand(Request $request, int $id): RedirectResponse
    {
        $dto = BrandDTO::fromRequest($request);
        $this->catalogService->updateBrand($id, $dto);

        return back()->with('success', 'Brand diperbarui.');
    }

    public function destroyBrand(Request $request, int $id): RedirectResponse
    {
        $this->catalogService->deleteBrand($id);

        return back()->with('success', 'Brand dihapus.');
    }

    public function storeLabel(Request $request): RedirectResponse
    {
        $dto = LabelDTO::fromRequest($request);
        $this->catalogService->createLabel($dto, $this->tenantId($request));

        return back()->with('success', 'Label berhasil ditambahkan.');
    }

    public function updateLabel(Request $request, int $id): RedirectResponse
    {
        $dto = LabelDTO::fromRequest($request);
        $this->catalogService->updateLabel($id, $dto);

        return back()->with('success', 'Label diperbarui.');
    }

    public function destroyLabel(Request $request, int $id): RedirectResponse
    {
        $this->catalogService->deleteLabel($id);

        return back()->with('success', 'Label dihapus.');
    }

    private function tenantId(Request $request): ?int
    {
        return $request->user()->primaryTenant?->id;
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
