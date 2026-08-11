<?php

namespace App\Repositories;

use App\Models\Withdrawal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    public function getAllWithdrawals(?string $status = null)
    {
        return Withdrawal::with(['store', 'wallet.tenant'])
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->get();
    }

    public function getWithdrawalsByTenantPaginated(int $tenantId, int $perPage, int $page): LengthAwarePaginator
    {
        return Withdrawal::where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'wpage', $page);
    }

    public function findOrFail(int $id): Withdrawal
    {
        return Withdrawal::findOrFail($id);
    }
}
