<?php

namespace App\Actions;

use App\Models\Store;
use App\Models\Tenant;
use App\Repositories\StoreRepository;

class CreateTenantAndStore
{
    public function __construct(
        protected StoreRepository $storeRepository
    ) {}

    /**
     * Provision a tenant + store for a newly registered seller.
     *
     * @return array{tenant: Tenant, store: Store}
     */
    public function __invoke(int $userId, string $storeName, string $slug): array
    {
        return $this->storeRepository->createTenantAndStore($userId, $storeName, $slug);
    }
}
