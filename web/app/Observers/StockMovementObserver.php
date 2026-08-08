<?php

namespace App\Observers;

use App\Models\StockMovement;
use App\Observers\Concerns\RecordsActivity;

class StockMovementObserver
{
    use RecordsActivity;

    public function created(StockMovement $movement): void
    {
        $this->logActivity("stock_{$movement->type}", $movement, [
            'quantity' => $movement->quantity,
            'stock_before' => $movement->stock_before,
            'stock_after' => $movement->stock_after,
            'reason' => $movement->reason,
        ]);
    }
}
