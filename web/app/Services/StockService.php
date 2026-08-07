<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockService
{
    public function stockIn(Product $product, int $quantity, ?string $reason = null, ?User $user = null): StockMovement
    {
        return $this->move($product, StockMovement::TYPE_IN, $quantity, $reason, $user);
    }

    public function stockOut(Product $product, int $quantity, ?string $reason = null, ?User $user = null): StockMovement
    {
        return $this->move($product, StockMovement::TYPE_OUT, $quantity, $reason, $user);
    }

    public function adjust(Product $product, int $newStock, ?string $reason = null, ?User $user = null): StockMovement
    {
        return DB::transaction(function () use ($product, $newStock, $reason, $user) {
            /** @var Product $locked */
            $locked = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();

            $newStock = max(0, $newStock);
            $delta = $newStock - $locked->stock;

            if ($delta === 0) {
                throw new RuntimeException('Stok tidak berubah.');
            }

            $before = $locked->stock;
            $after = $newStock;
            $locked->update(['stock' => $newStock]);

            return $this->create(
                $locked,
                $delta > 0 ? StockMovement::TYPE_IN : StockMovement::TYPE_OUT,
                abs($delta),
                $before,
                $after,
                $reason ?: ($delta > 0 ? 'Penyesuaian manual' : 'Penyesuaian manual'),
                $user,
            );
        });
    }

    /**
     * @return array<int, mixed>
     */
    public function history(Product $product, int $limit = 25): array
    {
        return $product->stockMovements()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (StockMovement $m) => $this->format($m))
            ->values()
            ->all();
    }

    private function move(Product $product, string $type, int $quantity, ?string $reason, ?User $user): StockMovement
    {
        return DB::transaction(function () use ($product, $type, $quantity, $reason, $user) {
            /** @var Product $locked */
            $locked = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();

            $quantity = max(1, $quantity);

            if ($type === StockMovement::TYPE_OUT && $locked->stock < $quantity) {
                throw new RuntimeException('Stok tidak mencukupi untuk pengeluaran ini.');
            }

            $before = $locked->stock;
            $after = $type === StockMovement::TYPE_IN ? $before + $quantity : $before - $quantity;

            $locked->update(['stock' => $after]);

            $defaultReason = $type === StockMovement::TYPE_IN ? 'Stok masuk' : 'Stok keluar';

            return $this->create($locked, $type, $quantity, $before, $after, $reason ?: $defaultReason, $user);
        });
    }

    private function create(Product $product, string $type, int $quantity, int $before, int $after, string $reason, ?User $user): StockMovement
    {
        return StockMovement::create([
            'product_id' => $product->id,
            'user_id' => $user?->id,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $before,
            'stock_after' => $after,
            'reason' => $reason,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function format(StockMovement $movement): array
    {
        $direction = $movement->type === StockMovement::TYPE_OUT ? -1 : 1;

        return [
            'id' => $movement->id,
            'type' => $movement->type,
            'quantity' => $movement->quantity,
            'delta' => $direction * $movement->quantity,
            'stock_before' => $movement->stock_before,
            'stock_after' => $movement->stock_after,
            'reason' => $movement->reason,
            'created_at' => $movement->created_at?->format('d M Y, H:i'),
        ];
    }
}
