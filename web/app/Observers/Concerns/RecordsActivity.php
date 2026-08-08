<?php

namespace App\Observers\Concerns;

use App\Models\User;
use App\Services\ActivityLogService;

/**
 * Shared helper to write activity log entries from observers.
 */
trait RecordsActivity
{
    protected function logActivity(
        string $action,
        object $subject,
        array $properties = []
    ): void {
        /** @var User|null $user */
        $user = auth()->user();

        app(ActivityLogService::class)->record(
            action: $action,
            subjectType: $subject::class,
            subjectId: $subject->getKey(),
            properties: $properties,
            user: $user,
        );
    }
}
