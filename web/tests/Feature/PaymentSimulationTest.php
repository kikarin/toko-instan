<?php

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Mail;

function paymentSimulationContext(): array
{
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'name' => 'Produk Bayar',
        'sku' => 'SKU-PAY-001',
        'price' => 100000,
        'stock' => 10,
    ]);

    return compact('seller', 'tenant', 'store', 'product');
}

test('checkout mengarahkan tamu ke halaman pembayaran simulasi', function () {
    ['store' => $store, 'product' => $product] = paymentSimulationContext();

    $this->post("/{$store->slug}/checkout", [
        'customer_name' => 'Pembayar',
        'customer_email' => 'pay@example.com',
        'customer_phone' => '0816666666',
        'shipping_address' => 'Jl. Bayar 1, Jakarta',
        'items' => [
            ['id' => $product->id, 'qty' => 1],
        ],
    ])->assertRedirect(route('payment.simulate', ['orderNumber' => Order::query()->latest('id')->first()->order_number]));
});

test('halaman pembayaran dapat diakses tamu', function () {
    ['store' => $store, 'product' => $product] = paymentSimulationContext();

    $this->post("/{$store->slug}/checkout", [
        'customer_name' => 'Pembayar',
        'customer_email' => 'pay2@example.com',
        'customer_phone' => '0816677777',
        'shipping_address' => 'Jl. Bayar 2, Jakarta',
        'items' => [
            ['id' => $product->id, 'qty' => 1],
        ],
    ]);

    $order = Order::query()->latest('id')->first();

    $this->get("/pay/{$order->order_number}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Payment/Pay'));
});

test('konfirmasi pembayaran menandai order paid dan mengkredit escrow', function () {
    Mail::fake();

    ['store' => $store, 'tenant' => $tenant, 'product' => $product] = paymentSimulationContext();

    $this->post("/{$store->slug}/checkout", [
        'customer_name' => 'Pembayar',
        'customer_email' => 'pay3@example.com',
        'customer_phone' => '0816688888',
        'shipping_address' => 'Jl. Bayar 3, Jakarta',
        'items' => [
            ['id' => $product->id, 'qty' => 2],
        ],
    ]);

    $order = Order::query()->latest('id')->first();

    $this->post("/pay/{$order->order_number}/confirm")
        ->assertRedirect(route('orders.success', ['store_slug' => $store->slug, 'orderNumber' => $order->order_number]));

    $order->refresh();

    expect($order->status)->toBe('paid')
        ->and($order->paid_at)->not->toBeNull();

    $wallet = Wallet::where('tenant_id', $tenant->id)->first();

    expect($wallet)->not->toBeNull()
        ->and((float) $wallet->pending_balance)->toBe(215000.0);

    $this->assertDatabaseHas('wallet_transactions', [
        'tenant_id' => $tenant->id,
        'type' => 'order_escrow',
        'amount' => 215000,
    ]);

    Mail::assertQueued(OrderConfirmationMail::class);
});

test('konfirmasi pembayaran idempotent — tidak double credit', function () {
    Mail::fake();

    ['store' => $store, 'tenant' => $tenant, 'product' => $product] = paymentSimulationContext();

    $this->post("/{$store->slug}/checkout", [
        'customer_name' => 'Pembayar',
        'customer_email' => 'pay4@example.com',
        'customer_phone' => '0816699999',
        'shipping_address' => 'Jl. Bayar 4, Jakarta',
        'items' => [
            ['id' => $product->id, 'qty' => 1],
        ],
    ]);

    $order = Order::query()->latest('id')->first();

    $this->post("/pay/{$order->order_number}/confirm")->assertRedirect();
    $this->post("/pay/{$order->order_number}/confirm")->assertRedirect();

    $wallet = Wallet::where('tenant_id', $tenant->id)->first();

    expect((float) $wallet->pending_balance)->toBe(115000.0);

    $count = WalletTransaction::where('tenant_id', $tenant->id)
        ->where('type', 'order_escrow')
        ->where('reference_id', $order->id)
        ->count();

    expect($count)->toBe(1);
});
