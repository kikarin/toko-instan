<?php

use App\Enums\WalletTransactionType;
use App\Enums\WithdrawalStatus;
use App\Models\Order;
use App\Models\Store;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\OrderService;
use App\Services\WalletService;
use App\Services\WithdrawService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
function makePaidOrder(float $total = 100000): array
{
    $store = Store::factory()->create();
    $store->tenant->update(['plan' => 'free']);

    $order = Order::factory()->create([
        'store_id' => $store->id,
        'total_amount' => $total,
        'status' => 'pending',
    ]);

    return [$store, $order];
}

function walletForStore(Store $store): Wallet
{
    return app(WalletService::class)->ensureForTenant($store->tenant_id);
}

test('order paid credits pending escrow and writes ledger snapshot', function () {
    [$store, $order] = makePaidOrder(150000);

    app(OrderService::class)->markOrderPaid($order);

    $wallet = walletForStore($store)->refresh();

    expect((float) $wallet->pending_balance)->toBe(150000.0)
        ->and((float) $wallet->balance)->toBe(0.0);

    $ledger = WalletTransaction::where('wallet_id', $wallet->id)->get();

    expect($ledger)->toHaveCount(1)
        ->and($ledger->first()->type)->toBe(WalletTransactionType::OrderEscrow->value)
        ->and((float) $ledger->first()->balance_after)->toBe(0.0)
        ->and((float) $ledger->first()->pending_after)->toBe(150000.0)
        ->and($ledger->first()->reference_type)->toBe(Wallet::class.'_order');
});

test('order escrow credit is idempotent', function () {
    [$store, $order] = makePaidOrder(150000);

    $orderService = app(OrderService::class);
    $orderService->markOrderPaid($order);
    $orderService->markOrderPaid($order);
    $orderService->markOrderPaid($order);

    $wallet = walletForStore($store)->refresh();

    expect((float) $wallet->pending_balance)->toBe(150000.0);
    expect(WalletTransaction::where('wallet_id', $wallet->id)->count())->toBe(1);
});

test('order completed releases escrow to available with two ledger rows', function () {
    [$store, $order] = makePaidOrder(200000);

    $orderService = app(OrderService::class);
    $orderService->markOrderPaid($order);
    $orderService->markOrderCompleted($order);

    $wallet = walletForStore($store)->refresh();

    expect((float) $wallet->pending_balance)->toBe(0.0)
        ->and((float) $wallet->balance)->toBe(200000.0);

    $types = WalletTransaction::where('wallet_id', $wallet->id)
        ->pluck('type')
        ->all();

    expect($types)->toContain(WalletTransactionType::OrderReleasePending->value)
        ->and($types)->toContain(WalletTransactionType::OrderReleaseAvailable->value);
});

test('withdraw request holds balance and records free-plan fee', function () {
    [$store, $order] = makePaidOrder(100000);

    $orderService = app(OrderService::class);
    $orderService->markOrderPaid($order);
    $orderService->markOrderCompleted($order);

    $wallet = walletForStore($store)->refresh();

    $withdrawal = app(WithdrawService::class)->request(
        $wallet,
        100000,
        ['bank_name' => 'BCA', 'account_number' => '1234', 'account_name' => 'Budi'],
        $store->id
    );

    expect($withdrawal->status)->toBe(WithdrawalStatus::Pending->value)
        ->and((float) $withdrawal->fee)->toBe(5000.0)
        ->and((float) $withdrawal->net_amount)->toBe(95000.0);

    $wallet->refresh();

    expect((float) $wallet->balance)->toBe(0.0);

    $types = WalletTransaction::where('wallet_id', $wallet->id)
        ->pluck('type')
        ->all();

    expect($types)->toContain(WalletTransactionType::WithdrawHold->value)
        ->and($types)->toContain(WalletTransactionType::WithdrawFee->value);
});

test('withdraw request above balance is rejected', function () {
    [$store, $order] = makePaidOrder(100000);

    $orderService = app(OrderService::class);
    $orderService->markOrderPaid($order);
    $orderService->markOrderCompleted($order);

    $wallet = walletForStore($store)->refresh();

    expect(fn () => app(WithdrawService::class)->request(
        $wallet,
        150000,
        ['bank_name' => 'BCA', 'account_number' => '1234', 'account_name' => 'Budi']
    ))->toThrow(InvalidArgumentException::class);
});

test('rejected withdraw releases hold back to balance', function () {
    [$store, $order] = makePaidOrder(100000);

    $orderService = app(OrderService::class);
    $orderService->markOrderPaid($order);
    $orderService->markOrderCompleted($order);

    $wallet = walletForStore($store)->refresh();

    $withdrawal = app(WithdrawService::class)->request(
        $wallet,
        100000,
        ['bank_name' => 'BCA', 'account_number' => '1234', 'account_name' => 'Budi'],
        $store->id
    );

    app(WithdrawService::class)->reject($withdrawal, 'Data rekening tidak valid');

    $wallet->refresh();

    expect((float) $wallet->balance)->toBe(100000.0)
        ->and($withdrawal->refresh()->status)->toBe(WithdrawalStatus::Rejected->value)
        ->and($withdrawal->refresh()->rejected_reason)->toBe('Data rekening tidak valid');
});

test('withdraw approve and transfer transitions', function () {
    [$store, $order] = makePaidOrder(100000);

    $orderService = app(OrderService::class);
    $orderService->markOrderPaid($order);
    $orderService->markOrderCompleted($order);

    $wallet = walletForStore($store)->refresh();

    $withdrawService = app(WithdrawService::class);
    $withdrawal = $withdrawService->request(
        $wallet,
        100000,
        ['bank_name' => 'BCA', 'account_number' => '1234', 'account_name' => 'Budi'],
        $store->id
    );

    $withdrawService->approve($withdrawal);
    expect($withdrawal->refresh()->status)->toBe(WithdrawalStatus::Approved->value)
        ->and($withdrawal->refresh()->approved_at)->not->toBeNull();

    $withdrawService->markTransferred($withdrawal);
    expect($withdrawal->refresh()->status)->toBe(WithdrawalStatus::Transferred->value)
        ->and($withdrawal->refresh()->transferred_at)->not->toBeNull();
});

test('premium plan has zero withdraw fee', function () {
    $store = Store::factory()->create();
    $store->tenant->update(['plan' => 'premium']);

    $order = Order::factory()->create([
        'store_id' => $store->id,
        'total_amount' => 100000,
        'status' => 'pending',
    ]);

    $orderService = app(OrderService::class);
    $orderService->markOrderPaid($order);
    $orderService->markOrderCompleted($order);

    $wallet = walletForStore($store)->refresh();

    $withdrawal = app(WithdrawService::class)->request(
        $wallet,
        100000,
        ['bank_name' => 'BCA', 'account_number' => '1234', 'account_name' => 'Budi'],
        $store->id
    );

    expect((float) $withdrawal->fee)->toBe(0.0)
        ->and((float) $withdrawal->net_amount)->toBe(100000.0);

    $wallet->refresh();
    expect((float) $wallet->balance)->toBe(0.0)
        ->and((float) $wallet->pending_balance)->toBe(0.0);

    expect(WalletTransaction::where('wallet_id', $wallet->id)->pluck('type')->all())
        ->toContain(WalletTransactionType::OrderDirect->value)
        ->not->toContain(WalletTransactionType::OrderEscrow->value);
});
