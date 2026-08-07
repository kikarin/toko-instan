<?php

namespace App\Services;

use App\Enums\WalletTransactionDirection;
use App\Enums\WalletTransactionType;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Penulisan ledger uang (immutable) sesuai docs/architecture/money-flow.md.
 *
 * Aturan emas:
 * 1. Tidak update balance/pending_balance tanpa baris ledger.
 * 2. Ledger immutable — koreksi lewat transaksi reversal baru, bukan edit.
 * 3. Setiap transaksi mencatat balance_after & pending_after.
 */
class WalletService
{
    public function ensureForTenant(int $tenantId): Wallet
    {
        return Wallet::firstOrCreate(['tenant_id' => $tenantId], [
            'balance' => 0,
            'pending_balance' => 0,
            'currency' => 'IDR',
        ]);
    }

    /**
     * Order dibayar -> pending_balance naik (escrow). Idempotent per order.
     */
    public function creditOrderEscrow(
        int $walletId,
        float $amount,
        int $orderId,
        string $description = 'Pending escrow penjualan'
    ): WalletTransaction {
        $reference = [Wallet::class.'_order', $orderId];

        $existing = $this->ledgerRow(WalletTransactionType::OrderEscrow, $reference);
        if ($existing) {
            return $existing;
        }

        return $this->commit(
            $walletId,
            type: WalletTransactionType::OrderEscrow,
            amount: $amount,
            applyToBalance: false,
            applyToPending: true,
            isCredit: true,
            reference: $reference,
            description: $description
        );
    }

    /**
     * Order completed -> pending turun, balance naik. Idempotent per order.
     */
    public function releaseEscrowToAvailable(int $walletId, float $amount, int $orderId): void
    {
        $reference = [Wallet::class.'_order', $orderId];

        if (WalletTransaction::where('wallet_id', $walletId)
            ->where('reference_type', $reference[0])
            ->where('reference_id', $reference[1])
            ->where('type', WalletTransactionType::OrderReleaseAvailable->value)
            ->exists()) {
            return;
        }

        DB::transaction(function () use ($walletId, $amount, $reference) {
            $wallet = $this->lockWallet($walletId);

            $this->insertLedgerRow(
                $wallet,
                WalletTransactionType::OrderReleasePending,
                WalletTransactionDirection::Debit,
                $amount,
                0,
                -$amount,
                $reference,
                'Order selesai - lepas escrow'
            );

            $this->insertLedgerRow(
                $wallet,
                WalletTransactionType::OrderReleaseAvailable,
                WalletTransactionDirection::Credit,
                $amount,
                $amount,
                0,
                $reference,
                'Order selesai - dana tersedia'
            );
        });
    }

    /**
     * Request withdraw -> hold saldo (balance -= amount).
     */
    public function holdForWithdraw(int $walletId, float $amount, int $withdrawalId): WalletTransaction
    {
        $reference = [Withdrawal::class, $withdrawalId];

        $existing = $this->ledgerRow(WalletTransactionType::WithdrawHold, $reference);
        if ($existing) {
            return $existing;
        }

        return $this->commit(
            $walletId,
            type: WalletTransactionType::WithdrawHold,
            amount: $amount,
            applyToBalance: true,
            applyToPending: false,
            reference: $reference,
            description: 'Withdraw di-hold'
        );
    }

    /**
     * Withdraw ditolak -> kembalikan hold ke balance.
     */
    public function releaseWithdrawalHold(int $walletId, float $amount, int $withdrawalId): WalletTransaction
    {
        $reference = [Withdrawal::class, $withdrawalId];

        $existing = $this->ledgerRow(WalletTransactionType::WithdrawRelease, $reference);
        if ($existing) {
            return $existing;
        }

        return $this->commit(
            $walletId,
            type: WalletTransactionType::WithdrawRelease,
            amount: $amount,
            applyToBalance: true,
            applyToPending: false,
            isCredit: true,
            reference: $reference,
            description: 'Withdraw ditolak - dana dikembalikan'
        );
    }

    /**
     * Catat fee withdraw (Rp5.000 free / 0 premium) sebagai baris ledger audit.
     * Fee tidak menggandakan pengurangan balance (sudah di-hold penuh di WithdrawHold).
     */
    public function recordWithdrawalFee(int $walletId, float $fee, int $withdrawalId): WalletTransaction
    {
        $reference = [Withdrawal::class, $withdrawalId];

        $existing = $this->ledgerRow(WalletTransactionType::WithdrawFee, $reference);
        if ($existing) {
            return $existing;
        }

        return $this->commit(
            $walletId,
            type: WalletTransactionType::WithdrawFee,
            amount: $fee,
            applyToBalance: false,
            applyToPending: false,
            isCredit: false,
            reference: $reference,
            description: 'Biaya penarikan'
        );
    }

    /**
     * @param  array{0: string, 1: int|string}  $reference
     */
    private function ledgerRow(WalletTransactionType $type, array $reference): ?WalletTransaction
    {
        return WalletTransaction::where('reference_type', $reference[0])
            ->where('reference_id', $reference[1])
            ->where('type', $type->value)
            ->first();
    }

    /**
     * @param  array{0: string, 1: int|string}  $reference
     */
    private function commit(
        int $walletId,
        WalletTransactionType $type,
        float $amount,
        bool $applyToBalance,
        bool $applyToPending,
        array $reference,
        bool $isCredit = false,
        ?string $description = null
    ): WalletTransaction {
        return DB::transaction(function () use (
            $walletId,
            $type,
            $amount,
            $applyToBalance,
            $applyToPending,
            $isCredit,
            $reference,
            $description
        ) {
            $wallet = $this->lockWallet($walletId);

            $dir = $isCredit
                ? WalletTransactionDirection::Credit
                : WalletTransactionDirection::Debit;

            $balanceDelta = $applyToBalance ? ($isCredit ? $amount : -$amount) : 0;
            $pendingDelta = $applyToPending ? ($isCredit ? $amount : -$amount) : 0;

            return $this->insertLedgerRow(
                $wallet,
                $type,
                $dir,
                $amount,
                $balanceDelta,
                $pendingDelta,
                $reference,
                $description
            );
        });
    }

    private function lockWallet(int $walletId): Wallet
    {
        return Wallet::whereKey($walletId)->lockForUpdate()->firstOrFail();
    }

    /**
     * @param  array{0: string, 1: int|string}  $reference
     */
    private function insertLedgerRow(
        Wallet $wallet,
        WalletTransactionType $type,
        WalletTransactionDirection $direction,
        float $amount,
        float $balanceDelta,
        float $pendingDelta,
        array $reference,
        ?string $description
    ): WalletTransaction {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount harus non-negatif.');
        }

        $newBalance = ((float) $wallet->balance) + $balanceDelta;
        $newPending = ((float) $wallet->pending_balance) + $pendingDelta;

        if ($newBalance < 0 || $newPending < 0) {
            throw new InvalidArgumentException('Saldo tidak mencukupi.');
        }

        $wallet->update([
            'balance' => round($newBalance, 2),
            'pending_balance' => round($newPending, 2),
        ]);

        return $wallet->transactions()->create([
            'tenant_id' => $wallet->tenant_id,
            'type' => $type->value,
            'direction' => $direction->value,
            'amount' => round($amount, 2),
            'balance_after' => round($newBalance, 2),
            'pending_after' => round($newPending, 2),
            'reference_type' => $reference[0],
            'reference_id' => $reference[1],
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
