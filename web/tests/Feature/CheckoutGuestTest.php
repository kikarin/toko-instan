<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;

function guestCheckoutContext(): array
{
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'name' => 'Produk Tamu',
        'sku' => 'SKU-GUEST-001',
        'price' => 100000,
        'stock' => 10,
    ]);

    return compact('seller', 'tenant', 'store', 'product');
}

test('guest dapat membuka halaman checkout tanpa login', function () {
    ['store' => $store] = guestCheckoutContext();

    $this->get("/{$store->slug}/checkout")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Checkout')->where('addresses', []));
});

test('guest dapat checkout tanpa login dan customer tercatat di tabel customers', function () {
    ['store' => $store, 'product' => $product] = guestCheckoutContext();

    $this->post("/{$store->slug}/checkout", [
        'customer_name' => 'Tamu Satu',
        'customer_email' => 'tamu@example.com',
        'customer_phone' => '0811111111',
        'shipping_address' => 'Jl. Tamu 1, Jakarta',
        'shipping_courier' => 'J&T Express',
        'payment_method' => 'qris',
        'items' => [
            ['id' => $product->id, 'qty' => 1],
        ],
    ])->assertRedirect();

    $order = Order::query()->latest('id')->first();
    $customer = Customer::query()->where('email', 'tamu@example.com')->first();

    expect($order)->not->toBeNull()
        ->and($order->customer_id)->not->toBeNull()
        ->and($order->shipping_courier)->toBe('J&T Express')
        ->and($order->payment_method)->toBe('qris')
        ->and($customer)->not->toBeNull()
        ->and($customer->user_id)->toBeNull()
        ->and($customer->store_id)->toBe($store->id);
});

test('checkout berulang dengan email sama memakai customer yang sama', function () {
    ['store' => $store, 'product' => $product] = guestCheckoutContext();

    foreach (range(1, 2) as $_) {
        $this->post("/{$store->slug}/checkout", [
            'customer_name' => 'Tamu Dua',
            'customer_email' => 'tamu2@example.com',
            'customer_phone' => '0812222222',
            'shipping_address' => 'Jl. Tamu 2, Bandung',
            'items' => [
                ['id' => $product->id, 'qty' => 1],
            ],
        ])->assertRedirect();
    }

    expect(Customer::where('email', 'tamu2@example.com')->count())->toBe(1);

    $orders = Order::where('customer_email', 'tamu2@example.com')->get();
    expect($orders->pluck('customer_id')->unique()->count())->toBe(1);
});

test('buyer login yang checkout ter-link ke user_id pada customer', function () {
    ['store' => $store, 'product' => $product] = guestCheckoutContext();

    $buyer = User::factory()->create([
        'role' => 'buyer',
        'email' => 'buyer-login@example.com',
        'store_id' => $store->id,
    ]);

    $this->actingAs($buyer)->post("/{$store->slug}/checkout", [
        'customer_name' => 'Buyer Login',
        'customer_email' => $buyer->email,
        'customer_phone' => '0813333333',
        'shipping_address' => 'Jl. Login 1, Surabaya',
        'items' => [
            ['id' => $product->id, 'qty' => 1],
        ],
    ])->assertRedirect();

    $customer = Customer::where('email', $buyer->email)->first();

    expect($customer)->not->toBeNull()
        ->and($customer->user_id)->toBe($buyer->id);
});

test('guest yang beli lalu login dengan email sama akan ter-link akunnya', function () {
    ['store' => $store, 'product' => $product] = guestCheckoutContext();

    $this->post("/{$store->slug}/checkout", [
        'customer_name' => 'Tamu Nanti Login',
        'customer_email' => 'nanti-login@example.com',
        'customer_phone' => '0814444444',
        'shipping_address' => 'Jl. Nanti 1, Jakarta',
        'items' => [
            ['id' => $product->id, 'qty' => 1],
        ],
    ])->assertRedirect();

    $buyer = User::factory()->create([
        'role' => 'buyer',
        'email' => 'nanti-login@example.com',
        'store_id' => $store->id,
    ]);

    $this->actingAs($buyer)->post("/{$store->slug}/checkout", [
        'customer_name' => 'Tamu Nanti Login',
        'customer_email' => 'nanti-login@example.com',
        'customer_phone' => '0814444444',
        'shipping_address' => 'Jl. Nanti 1, Jakarta',
        'items' => [
            ['id' => $product->id, 'qty' => 1],
        ],
    ])->assertRedirect();

    $customer = Customer::where('email', 'nanti-login@example.com')->first();

    expect($customer->user_id)->toBe($buyer->id)
        ->and(Customer::where('email', 'nanti-login@example.com')->count())->toBe(1);
});

test('halaman sukses pesanan bisa diakses tamu', function () {
    ['store' => $store, 'product' => $product] = guestCheckoutContext();

    $this->post("/{$store->slug}/checkout", [
        'customer_name' => 'Tamu Sukses',
        'customer_email' => 'sukses@example.com',
        'customer_phone' => '0815555555',
        'shipping_address' => 'Jl. Sukses 1, Jakarta',
        'items' => [
            ['id' => $product->id, 'qty' => 1],
        ],
    ])->assertRedirect();

    $order = Order::query()->latest('id')->first();

    $this->get("/{$store->slug}/orders/{$order->order_number}/success")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('OrderSuccess'));
});
