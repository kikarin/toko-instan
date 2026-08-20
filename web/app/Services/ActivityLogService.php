<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use App\Repositories\ActivityLogRepository;
use App\Repositories\StoreRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogService
{
    public function __construct(
        protected TenantContext $tenants,
        protected ActivityLogRepository $logRepository,
        protected StoreRepository $storeRepository
    ) {}

    /**
     * Record a new audit log entry.
     *
     * @param  array<string, mixed>  $properties
     */
    public function record(
        string $action,
        ?string $subjectType = null,
        string|int|null $subjectId = null,
        array $properties = [],
        ?User $user = null,
        ?string $ip = null
    ): ActivityLog {
        $user ??= auth()->user();

        return $this->logRepository->createLog([
            'tenant_id' => $this->tenantIdFor($user),
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId !== null ? (string) $subjectId : null,
            'properties' => $properties,
            'ip' => $ip ?? request()->ip(),
        ]);
    }

    public function getLogsForUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        $store = $this->storeRepository->getStoreForUser($userId);

        if (! $store || ! $store->tenant_id) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        return $this->logRepository->getLogsByTenant($store->tenant_id, $perPage);
    }

    /**
     * Resolve the tenant the log belongs to.
     *
     * The resolved tenant context only exists on storefront (subdomain)
     * requests. On the seller dashboard (platform domain) the tenant is
     * derived from the authenticated user's primary store.
     */
    protected function tenantIdFor(?User $user): ?int
    {
        if ($this->tenants->hasTenant()) {
            return $this->tenants->id();
        }

        if (! $user) {
            return null;
        }

        return $this->storeRepository->getStoreForUser($user->id)?->tenant_id;
    }
}
