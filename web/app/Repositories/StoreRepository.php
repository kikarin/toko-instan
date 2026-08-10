<?php

namespace App\Repositories;

use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class StoreRepository
{
    public function getPrimaryStore(): ?Store
    {
        return Store::first();
    }

    public function getStoreForUser(int $userId): ?Store
    {
        return Store::whereHas('tenant', fn ($q) => $q->where('user_id', $userId))->first();
    }

    public function getActiveStore(?int $userId = null): ?Store
    {
        if ($userId) {
            $user = User::find($userId);
            if ($user && $user->role === 'buyer' && $user->store_id) {
                return Store::find($user->store_id);
            }

            return $this->getStoreForUser($userId) ?: $this->getPrimaryStoreWithProducts();
        }

        return $this->getPrimaryStoreWithProducts();
    }

    private function getPrimaryStoreWithProducts(): ?Store
    {
        return Store::withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderByDesc('products_count')
            ->orderBy('id')
            ->first();
    }

    public function findBySlug(string $slug): ?Store
    {
        return Store::where('slug', $slug)->first();
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
     * @return Collection<int, Store>
     */
    public function getLatest(int $limit = 5): Collection
    {
        return Store::orderByDesc('created_at')->take($limit)->get();
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

    public function incrementTotalOrders(int $storeId): void
    {
        $store = Store::find($storeId);
        if ($store) {
            $store->increment('total_orders', 1);
        }
    }
}
