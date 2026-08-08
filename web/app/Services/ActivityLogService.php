<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Store;
use App\Models\User;

class ActivityLogService
{
    public function __construct(protected TenantContext $tenants) {}

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

        return ActivityLog::create([
            'tenant_id' => $this->tenantIdFor($user),
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId !== null ? (string) $subjectId : null,
            'properties' => $properties,
            'ip' => $ip ?? request()->ip(),
        ]);
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

        return Store::whereHas(
            'tenant',
            fn ($q) => $q->where('user_id', $user->id)
        )->value('tenant_id');
    }
}
