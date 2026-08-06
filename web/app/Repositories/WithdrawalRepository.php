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
}
