<?php

namespace App\Enums;

/**
 * Tipe transaksi wallet — lihat docs/architecture/money-flow.md.
 */
enum WalletTransactionType: string
{
    case OrderDirect = 'order_direct';
    case OrderEscrow = 'order_escrow';
    case OrderReleasePending = 'order_release_pending';
    case OrderReleaseAvailable = 'order_release_available';
    case OrderRefund = 'order_refund';
    case WithdrawHold = 'withdraw_hold';
    case WithdrawFee = 'withdraw_fee';
    case WithdrawRelease = 'withdraw_release';
    case WithdrawPaid = 'withdraw_paid';
    case Adjustment = 'adjustment';
    case Referral = 'referral';
}
