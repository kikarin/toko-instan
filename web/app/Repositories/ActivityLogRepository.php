<?php

namespace App\Repositories;

use App\Models\ActivityLog;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogRepository
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createLog(array $data): ActivityLog
    {
        return ActivityLog::create($data);
    }

    public function getLogsByTenant(int $tenantId, int $perPage = 20): LengthAwarePaginator
    {
        return ActivityLog::with('user')
            ->where('tenant_id', $tenantId)
            ->latest('created_at')
            ->paginate($perPage);
    }
}
