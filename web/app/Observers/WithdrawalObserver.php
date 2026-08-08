<?php

namespace App\Observers;

use App\Models\Withdrawal;
use App\Observers\Concerns\RecordsActivity;

class WithdrawalObserver
{
    use RecordsActivity;

    public function created(Withdrawal $withdrawal): void
    {
        $this->logActivity('withdrawal_request', $withdrawal, [
            'amount' => $withdrawal->amount,
            'fee' => $withdrawal->fee,
            'net_amount' => $withdrawal->net_amount,
            'status' => $withdrawal->status,
        ]);
    }

    public function updated(Withdrawal $withdrawal): void
    {
        $changes = $withdrawal->getChanges();
        unset($changes['updated_at']);

        if ($changes === [] || ! isset($changes['status'])) {
            return;
        }

        $this->logActivity('withdrawal_status', $withdrawal, [
            'from' => $withdrawal->getOriginal('status'),
            'to' => $changes['status'],
        ]);
    }
}
