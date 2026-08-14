<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Wallet;

test('checkout with cod creates cod payment without midtrans', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);
    $buyer = User::factory()->create(['role' => 'buyer', 'store_id' => $store->id]);
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'price' => 40000,
        'stock' => 5,
        'is_active' => true,
        'type' => 'physical',
    ]);

    $this->actingAs($buyer)->post("/{$store->slug}/checkout", [
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'customer_phone' => '08123456789',
        'shipping_address' => 'Jl. COD',
        'shipping_courier' => 'JNE Reguler',
        'payment_method' => 'cod',
        'items' => [['id' => $product->id, 'qty' => 1]],
    ])->assertRedirect();

    $order = Order::query()->latest('id')->first();

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'provider' => PaymentProvider::Cod->value,
        'method' => PaymentMethod::Cod->value,
        'status' => PaymentStatus::Pending->value,
        'snap_token' => null,
    ]);
});

test('cod is rejected for digital-only order', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);
    $buyer = User::factory()->create(['role' => 'buyer', 'store_id' => $store->id]);
    $product = Product::factory()->digital()->create([
        'store_id' => $store->id,
        'price' => 25000,
        'stock' => 5,
        'is_active' => true,
    ]);

    $this->actingAs($buyer)->post("/{$store->slug}/checkout", [
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'customer_phone' => '08123456789',
        'shipping_address' => 'N/A',
        'payment_method' => 'cod',
        'items' => [['id' => $product->id, 'qty' => 1]],
    ])->assertSessionHasErrors('payment_method');
});

test('seller can confirm cod payment', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);
    Wallet::factory()->create(['tenant_id' => $tenant->id]);

    $order = Order::factory()->create([
        'store_id' => $store->id,
        'status' => 'pending',
        'payment_method' => 'cod',
        'total_amount' => 55000,
    ]);

    $payment = Payment::factory()->cod()->create([
        'order_id' => $order->id,
        'tenant_id' => $tenant->id,
        'amount' => 55000,
        'status' => PaymentStatus::Pending,
    ]);

    $this->actingAs($seller)
        ->post("/payments/{$payment->id}/confirm")
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('paid')
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Paid);
});
