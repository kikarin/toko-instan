<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository
{
    /**
     * @return Collection<int, Category>
     */
    public function getCategoriesByTenant(?int $tenantId)
    {
        return Category::where('tenant_id', $tenantId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function countByTenant(?int $tenantId): int
    {
        return Category::where('tenant_id', $tenantId)->count();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function findOrFail(int $id): Category
    {
        return Category::findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
