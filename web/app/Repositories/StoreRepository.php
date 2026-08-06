<?php

namespace App\Repositories;

use App\Models\Store;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

class StoreRepository
{
    public function getPrimaryStore(): ?Store
    {
        return Store::first();
    }

    /**
     * @return Collection<int, Store>
     */
    public function getTopSellers(int $limit = 5): Collection
    {
        return Store::orderByDesc('gmv')->take($limit)->get();
    }

    public function countActiveStores(): int
    {
        return Store::count();
    }

    /**
     * @return array{tenant: Tenant, store: Store}
     */
    public function createTenantAndStore(int $userId, string $storeName, string $slug): array
    {
        $tenant = Tenant::create([
            'user_id' => $userId,
            'name' => $storeName.' Tenant',
            'slug' => $slug.'-'.rand(100, 999),
            'plan' => 'free',
        ]);

        $store = Store::create([
            'tenant_id' => $tenant->id,
            'name' => $storeName,
            'slug' => $slug,
            'status' => 'active',
        ]);

        return ['tenant' => $tenant, 'store' => $store];
    }

    public function addPendingEscrow(int $storeId, float $amount): void
    {
        $store = Store::find($storeId) ?: Store::first();
        if ($store) {
            $store->increment('pending_escrow', $amount);
            $store->increment('total_orders', 1);
        }
    }
}
