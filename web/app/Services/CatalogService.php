<?php

namespace App\Services;

use App\DTO\Catalog\BrandDTO;
use App\DTO\Catalog\CategoryDTO;
use App\DTO\Catalog\LabelDTO;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Label;
use App\Repositories\BrandRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\LabelRepository;
use Illuminate\Support\Str;

class CatalogService
{
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected BrandRepository $brandRepository,
        protected LabelRepository $labelRepository
    ) {}

    public function getCategoriesByTenant(?int $tenantId)
    {
        return $this->categoryRepository->getCategoriesByTenant($tenantId);
    }

    public function getBrandsByTenant(?int $tenantId)
    {
        return $this->brandRepository->getBrandsByTenant($tenantId);
    }

    public function getLabelsByTenant(?int $tenantId)
    {
        return $this->labelRepository->getLabelsByTenant($tenantId);
    }

    public function createCategory(CategoryDTO $dto, ?int $tenantId): Category
    {
        return $this->categoryRepository->create([
            'tenant_id' => $tenantId,
            'name' => $dto->name,
            'slug' => Str::slug($dto->name).'-'.Str::random(4),
            'sort_order' => $this->categoryRepository->countByTenant($tenantId),
        ]);
    }

    public function updateCategory(int $id, CategoryDTO $dto): void
    {
        $category = $this->categoryRepository->findOrFail($id);
        $this->categoryRepository->update($category, ['name' => $dto->name]);
    }

    public function deleteCategory(int $id): void
    {
        $category = $this->categoryRepository->findOrFail($id);
        $this->categoryRepository->delete($category);
    }

    public function createBrand(BrandDTO $dto, ?int $tenantId): Brand
    {
        return $this->brandRepository->create([
            'tenant_id' => $tenantId,
            'name' => $dto->name,
            'slug' => Str::slug($dto->name).'-'.Str::random(4),
        ]);
    }

    public function updateBrand(int $id, BrandDTO $dto): void
    {
        $brand = $this->brandRepository->findOrFail($id);
        $this->brandRepository->update($brand, ['name' => $dto->name]);
    }

    public function deleteBrand(int $id): void
    {
        $brand = $this->brandRepository->findOrFail($id);
        $this->brandRepository->delete($brand);
    }

    public function createLabel(LabelDTO $dto, ?int $tenantId): Label
    {
        return $this->labelRepository->create([
            'tenant_id' => $tenantId,
            'name' => $dto->name,
            'slug' => Str::slug($dto->name).'-'.Str::random(4),
            'color' => $dto->color,
        ]);
    }

    public function updateLabel(int $id, LabelDTO $dto): void
    {
        $label = $this->labelRepository->findOrFail($id);
        $this->labelRepository->update($label, [
            'name' => $dto->name,
            'color' => $dto->color,
        ]);
    }

    public function deleteLabel(int $id): void
    {
        $label = $this->labelRepository->findOrFail($id);
        $this->labelRepository->delete($label);
    }
}
