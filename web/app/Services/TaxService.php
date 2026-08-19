<?php

namespace App\Services;

use App\Models\Store;

class TaxService
{
    public function rate(): float
    {
        return (float) config('tax.ppn_rate', 11);
    }

    public function ppnAmount(?Store $store, int $dpp): int
    {
        if (! $store?->is_pkp || $dpp <= 0) {
            return 0;
        }

        return (int) round($dpp * $this->rate() / 100);
    }
}
