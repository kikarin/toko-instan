<?php

namespace App\Services;

use App\Models\ActivityLog;
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
        return ActivityLog::create([
            'tenant_id' => $this->tenants->id(),
            'user_id' => $user?->id ?? auth()->id(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId !== null ? (string) $subjectId : null,
            'properties' => $properties,
            'ip' => $ip ?? request()->ip(),
        ]);
    }
}
