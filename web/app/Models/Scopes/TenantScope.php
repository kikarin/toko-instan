<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

/** @implements Scope<Model> */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = tenantId();

        if ($tenantId === null || ! Schema::hasColumn($model->getTable(), 'tenant_id')) {
            return;
        }

        $builder->where($model->getTable().'.tenant_id', $tenantId);
    }
}
