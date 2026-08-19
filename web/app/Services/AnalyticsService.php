<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StoreVisit;

class AnalyticsService
{
    /**
     * @return array{Hari: array{labels: list<string>, revenue: list<array{i: int, v: number}>, orders: list<int>, visitors: list<int>}, Minggu: array<string, mixed>, Bulan: array<string, mixed>}
     */
    public function chartsForStore(?int $storeId): array
    {
        return [
            'Hari' => $this->series($storeId, 14, 'day'),
            'Minggu' => $this->series($storeId, 12, 'week'),
            'Bulan' => $this->series($storeId, 12, 'month'),
        ];
    }

    public function visitorsToday(?int $storeId): int
    {
        if (! $storeId) {
            return 0;
        }

        return (int) StoreVisit::query()
            ->where('store_id', $storeId)
            ->whereDate('visited_on', today())
            ->distinct()
            ->count('session_key');
    }

    /**
     * @return array{labels: list<string>, revenue: list<array{i: int, v: int}>, orders: list<int>, visitors: list<int>}
     */
    protected function series(?int $storeId, int $points, string $unit): array
    {
        $labels = [];
        $revenue = [];
        $orders = [];
        $visitors = [];

        for ($i = $points - 1; $i >= 0; $i--) {
            $start = match ($unit) {
                'week' => now()->startOfWeek()->subWeeks($i),
                'month' => now()->startOfMonth()->subMonths($i),
                default => now()->startOfDay()->subDays($i),
            };
            $end = match ($unit) {
                'week' => (clone $start)->endOfWeek(),
                'month' => (clone $start)->endOfMonth(),
                default => (clone $start)->endOfDay(),
            };

            $labels[] = match ($unit) {
                'week' => $start->format('d M'),
                'month' => $start->translatedFormat('M Y'),
                default => $start->format('d M'),
            };

            $orderQuery = Order::query()
                ->when($storeId, fn ($q) => $q->where('store_id', $storeId))
                ->whereBetween('created_at', [$start, $end]);

            $rev = (int) round((float) (clone $orderQuery)->whereIn('status', ['paid', 'processing', 'packed', 'shipped', 'completed'])->sum('total_amount'));
            $cnt = (int) (clone $orderQuery)->count();

            $vis = 0;
            if ($storeId) {
                $vis = (int) StoreVisit::query()
                    ->where('store_id', $storeId)
                    ->whereBetween('visited_on', [$start->toDateString(), $end->toDateString()])
                    ->distinct()
                    ->count('session_key');
            }

            $idx = count($revenue);
            $revenue[] = ['i' => $idx, 'v' => $rev];
            $orders[] = $cnt;
            $visitors[] = $vis;
        }

        return compact('labels', 'revenue', 'orders', 'visitors');
    }
}
