<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.midtrans.server_key' => 'SB-Mid-server-test',
        'services.midtrans.client_key' => 'SB-Mid-client-test',
        'services.midtrans.is_production' => false,
    ]);
});

function webhookSignature(string $orderId, string $statusCode, string $grossAmount): string
{
    return hash('sha512', $orderId.$statusCode.$grossAmount.'SB-Mid-server-test');
}

test('midtrans webhook marks order paid with idempotency', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);
    Wallet::factory()->create(['tenant_id' => $tenant->id]);

    $order = Order::factory()->create([
        'store_id' => $store->id,
        'order_number' => 'ORD-WEBHOOK-1',
        'total_amount' => 100000,
        'status' => 'pending',
        'payment_method' => 'qris',
    ]);

    Payment::factory()->create([
        'order_id' => $order->id,
        'tenant_id' => $tenant->id,
        'provider' => PaymentProvider::Midtrans,
        'method' => PaymentMethod::Qris,
        'amount' => 100000,
        'status' => PaymentStatus::Pending,
        'idempotency_key' => 'init:ORD-WEBHOOK-1',
    ]);

    $payload = [
        'order_id' => 'ORD-WEBHOOK-1',
        'transaction_id' => 'trx-abc-1',
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'status_code' => '200',
        'gross_amount' => '100000.00',
    ];
    $payload['signature_key'] = webhookSignature(
        $payload['order_id'],
        $payload['status_code'],
        $payload['gross_amount'],
    );

    $this->postJson('/webhooks/midtrans', $payload)->assertOk();

    expect($order->fresh()->status)->toBe('paid');
    expect(Payment::where('order_id', $order->id)->where('status', 'paid')->exists())->toBeTrue();

    // Second delivery of same webhook must be idempotent
    $this->postJson('/webhooks/midtrans', $payload)->assertOk();
    expect(Payment::where('order_id', $order->id)->where('status', 'paid')->count())->toBe(1);
});

test('syncing midtrans status via API marks order paid', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);
    Wallet::factory()->create(['tenant_id' => $tenant->id]);

    $order = Order::factory()->create([
        'store_id' => $store->id,
        'order_number' => 'ORD-SYNC-1',
        'customer_email' => $buyer->email,
        'total_amount' => 100000,
        'status' => 'pending',
        'payment_method' => 'qris',
    ]);

    Payment::factory()->create([
        'order_id' => $order->id,
        'tenant_id' => $tenant->id,
        'provider' => PaymentProvider::Midtrans,
        'method' => PaymentMethod::Qris,
        'amount' => 100000,
        'status' => PaymentStatus::Pending,
        'idempotency_key' => 'init:ORD-SYNC-1',
    ]);

    Http::fake([
        'api.sandbox.midtrans.com/v2/ORD-SYNC-1/status' => Http::response([
            'status_code' => '200',
            'transaction_id' => 'trx-sync-1',
            'gross_amount' => '100000.00',
            'order_id' => 'ORD-SYNC-1',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
        ], 200),
    ]);

    $this->actingAs($buyer)
        ->post("/{$store->slug}/orders/{$order->order_number}/sync-payment")
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('paid');
    expect(Payment::where('order_id', $order->id)->where('status', 'paid')->exists())->toBeTrue();
});

test('midtrans webhook rejects invalid signature', function () {
    $this->postJson('/webhooks/midtrans', [
        'order_id' => 'ORD-X',
        'status_code' => '200',
        'gross_amount' => '1000.00',
        'signature_key' => 'invalid',
        'transaction_status' => 'settlement',
        'transaction_id' => 't1',
    ])->assertForbidden();
});
