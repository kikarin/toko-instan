<?php

namespace App\Observers;

use App\Models\Product;
use App\Observers\Concerns\RecordsActivity;

class ProductObserver
{
    use RecordsActivity;

    public function created(Product $product): void
    {
        $this->logActivity('created', $product, [
            'name' => $product->name,
            'price' => $product->price,
            'stock' => $product->stock,
        ]);
    }

    public function updated(Product $product): void
    {
        $changes = $product->getChanges();
        unset($changes['updated_at']);

        if ($changes === []) {
            return;
        }

        $this->logActivity('updated', $product, [
            'name' => $product->name,
            'changes' => $changes,
        ]);
    }

    public function deleted(Product $product): void
    {
        $this->logActivity('deleted', $product, [
            'name' => $product->name,
        ]);
    }
}
