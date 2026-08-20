<?php

namespace App\Repositories;

use App\Models\Label;
use Illuminate\Database\Eloquent\Collection;

class LabelRepository
{
    /**
     * @return Collection<int, Label>
     */
    public function getLabelsByTenant(?int $tenantId)
    {
        return Label::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Label
    {
        return Label::create($data);
    }

    public function findOrFail(int $id): Label
    {
        return Label::findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Label $label, array $data): bool
    {
        return $label->update($data);
    }

    public function delete(Label $label): bool
    {
        return $label->delete();
    }
}
