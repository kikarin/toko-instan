<?php

namespace App\Repositories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;

class BrandRepository
{
    /**
     * @return Collection<int, Brand>
     */
    public function getBrandsByTenant(?int $tenantId)
    {
        return Brand::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Brand
    {
        return Brand::create($data);
    }

    public function findOrFail(int $id): Brand
    {
        return Brand::findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Brand $brand, array $data): bool
    {
        return $brand->update($data);
    }

    public function delete(Brand $brand): bool
    {
        return $brand->delete();
    }
}
