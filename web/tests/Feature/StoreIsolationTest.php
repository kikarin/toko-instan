<?php

use App\Models\Order;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;

function storeIsolationContext(): array
{
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $storeA = Store::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Toko Nike',
        'slug' => 'nike-indonesia',
    ]);
    $storeB = Store::factory()->create([
        'name' => 'Toko Adidas',
        'slug' => 'adidas-indonesia',
    ]);

    $buyerA = User::factory()->create([
        'role' => 'buyer',
        'email' => 'buyer-nike@example.com',
        'store_id' => $storeA->id,
    ]);

    return compact('seller', 'tenant', 'storeA', 'storeB', 'buyerA');
}

test('guest dapat menjelajahi storefront toko mana pun', function () {
    ['storeA' => $storeA, 'storeB' => $storeB] = storeIsolationContext();

    $this->get("/{$storeA->slug}")->assertOk();
    $this->get("/{$storeB->slug}")->assertOk();

    $this->get("/{$storeA->slug}/checkout")->assertOk();
    $this->get("/{$storeB->slug}/checkout")->assertOk();
});

test('buyer dapat membuka storefront dan checkout toko miliknya', function () {
    ['buyerA' => $buyerA, 'storeA' => $storeA] = storeIsolationContext();

    $this->actingAs($buyerA)
        ->get("/{$storeA->slug}")
        ->assertOk();

    $this->actingAs($buyerA)
        ->get("/{$storeA->slug}/checkout")
        ->assertOk();
});

test('buyer diarahkan ke toko sendiri saat membuka toko lain', function () {
    ['buyerA' => $buyerA, 'storeA' => $storeA, 'storeB' => $storeB] = storeIsolationContext();

    $this->actingAs($buyerA)->get("/{$storeB->slug}")->assertRedirect("/{$storeA->slug}");
    $this->actingAs($buyerA)->get("/{$storeB->slug}/checkout")->assertRedirect("/{$storeA->slug}");
    $this->actingAs($buyerA)->get("/{$storeB->slug}/p/produk-lain")->assertRedirect("/{$storeA->slug}");
    $this->actingAs($buyerA)->get("/{$storeB->slug}/orders")->assertRedirect("/{$storeA->slug}");
});

test('seller tidak bisa membuka storefront toko lain', function () {
    ['seller' => $seller, 'storeB' => $storeB] = storeIsolationContext();

    $this->actingAs($seller)->get("/{$storeB->slug}")->assertRedirect('/dashboard');
    $this->actingAs($seller)->get("/{$storeB->slug}/checkout")->assertRedirect('/dashboard');
});

test('seller dapat preview storefront toko miliknya', function () {
    ['seller' => $seller, 'storeA' => $storeA] = storeIsolationContext();

    $this->actingAs($seller)->get("/{$storeA->slug}")->assertOk();
});

test('admin dapat preview storefront toko mana pun', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ['storeA' => $storeA, 'storeB' => $storeB] = storeIsolationContext();

    $this->actingAs($admin)->get("/{$storeA->slug}")->assertOk();
    $this->actingAs($admin)->get("/{$storeB->slug}")->assertOk();
});

test('pembayaran: owner boleh akses, guest boleh via nomor order, buyer lain ditolak', function () {
    ['buyerA' => $buyerA, 'storeA' => $storeA] = storeIsolationContext();

    $order = Order::create([
        'store_id' => $storeA->id,
        'order_number' => 'ORD-ISO-001',
        'customer_name' => 'Buyer Nike',
        'customer_email' => $buyerA->email,
        'customer_phone' => '081212121212',
        'shipping_address' => 'Jl. Isolasi 1, Jakarta',
        'total_amount' => 50000,
        'status' => 'pending',
    ]);

    $this->actingAs($buyerA)->get("/pay/{$order->order_number}")->assertOk();

    $this->get("/pay/{$order->order_number}")->assertOk();

    $otherBuyer = User::factory()->create([
        'role' => 'buyer',
        'email' => 'buyer-adidas@example.com',
    ]);

    $this->actingAs($otherBuyer)->get("/pay/{$order->order_number}")->assertForbidden();
});
