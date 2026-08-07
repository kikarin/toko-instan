<?php

namespace App\Services;

use App\Enums\WithdrawalStatus;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WithdrawService
{
    public const FREE_PLAN_FEE = 5000;

    public function __construct(
        protected WalletService $walletService
    ) {}

    public function feeFor(Wallet $wallet): float
    {
        $plan = $wallet->tenant?->plan;

        return $plan === null || $plan === 'free' ? self::FREE_PLAN_FEE : 0;
    }

    /**
     * @param  array{account_number: string, account_name: string, bank_name?: ?string}  $bankInfo
     */
    public function request(Wallet $wallet, float $amount, array $bankInfo, ?int $storeId = null): Withdrawal
    {
        $fee = $this->feeFor($wallet);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Jumlah penarikan harus lebih dari 0.');
        }

        if ($amount > (float) $wallet->balance) {
            throw new InvalidArgumentException('Saldo tidak mencukupi untuk penarikan.');
        }

        if ($amount <= $fee) {
            throw new InvalidArgumentException('Jumlah penarikan harus lebih besar dari biaya penarikan.');
        }

        return DB::transaction(function () use ($wallet, $amount, $fee, $bankInfo, $storeId) {
            $withdrawal = Withdrawal::create([
                'wallet_id' => $wallet->id,
                'tenant_id' => $wallet->tenant_id,
                'store_id' => $storeId,
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $amount - $fee,
                'bank_name' => $bankInfo['bank_name'] ?? '',
                'account_number' => $bankInfo['account_number'],
                'account_name' => $bankInfo['account_name'],
                'status' => WithdrawalStatus::Pending->value,
            ]);

            $this->walletService->holdForWithdraw($wallet->id, $amount, $withdrawal->id);

            if ($fee > 0) {
                $this->walletService->recordWithdrawalFee($wallet->id, $fee, $withdrawal->id);
            }

            return $withdrawal;
        });
    }

    public function approve(Withdrawal $withdrawal): void
    {
        if ($withdrawal->status !== WithdrawalStatus::Pending->value) {
            throw new InvalidArgumentException('Hanya penarikan pending yang bisa disetujui.');
        }

        $withdrawal->update([
            'status' => WithdrawalStatus::Approved->value,
            'approved_at' => now(),
        ]);
    }

    public function markTransferred(Withdrawal $withdrawal): void
    {
        $withdrawal->update([
            'status' => WithdrawalStatus::Transferred->value,
            'transferred_at' => now(),
        ]);
    }

    public function reject(Withdrawal $withdrawal, string $reason): void
    {
        if ($withdrawal->status !== WithdrawalStatus::Pending->value) {
            throw new InvalidArgumentException('Hanya penarikan pending yang bisa ditolak.');
        }

        DB::transaction(function () use ($withdrawal, $reason) {
            $this->walletService->releaseWithdrawalHold(
                (int) $withdrawal->wallet_id,
                (float) $withdrawal->amount,
                $withdrawal->id
            );

            $withdrawal->update([
                'status' => WithdrawalStatus::Rejected->value,
                'rejected_reason' => $reason,
            ]);
        });
    }
}
