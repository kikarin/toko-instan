<?php

namespace App\Observers;

use App\Models\Store;
use App\Observers\Concerns\RecordsActivity;

class StoreObserver
{
    use RecordsActivity;

    private const TRACKED_COLUMNS = [
        'theme',
        'theme_colors',
        'showcase',
        'name',
        'description',
        'headline',
        'banner_url',
        'logo',
        'phone',
        'email',
        'address',
        'instagram',
        'tiktok',
        'is_active',
    ];

    public function updated(Store $store): void
    {
        $changes = array_intersect_key($store->getChanges(), array_flip(self::TRACKED_COLUMNS));

        if ($changes === []) {
            return;
        }

        $this->logActivity('store_updated', $store, [
            'store' => $store->name,
            'changes' => $changes,
        ]);
    }
}
