<?php

namespace App\Repositories;

use App\Models\Withdrawal;

class WithdrawalRepository
{
    public function getTransferredSum(): float
    {
        return (float) Withdrawal::where('status', 'transferred')->sum('amount');
    }

    public function getLatestTransferred(): ?Withdrawal
    {
        return Withdrawal::where('status', 'transferred')->latest()->first();
    }

    public function countPending(): int
    {
        return (int) Withdrawal::where('status', 'pending')->count();
    }

    public function getPendingSum(): float
    {
        return (float) Withdrawal::where('status', 'pending')->sum('amount');
    }

    public function getTransferredSumForStore(?int $storeId): float
    {
        if ($storeId === null) {
            return 0.0;
        }

        return (float) Withdrawal::query()
            ->where('status', 'transferred')
            ->where('store_id', $storeId)
            ->sum('amount');
    }

    public function getLatestTransferredForStore(?int $storeId): ?Withdrawal
    {
        if ($storeId === null) {
            return null;
        }

        return Withdrawal::query()
            ->where('status', 'transferred')
            ->where('store_id', $storeId)
            ->latest()
            ->first();
    }
}
