<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;

test('404 dirender sebagai halaman error Inertia', function () {
    $this->get('/halaman-tidak-ada')
        ->assertStatus(404)
        ->assertInertia(fn ($page) => $page
            ->component('Error')
            ->where('status', 404));
});

test('403 dirender sebagai halaman error Inertia', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);

    $buyer = User::factory()->create(['role' => 'buyer', 'email' => 'pemilik-order@example.com']);
    $otherBuyer = User::factory()->create(['role' => 'buyer', 'email' => 'orang-lain@example.com']);

    Product::factory()->create(['store_id' => $store->id]);

    Order::create([
        'store_id' => $store->id,
        'order_number' => 'ERR-403-001',
        'customer_name' => 'Pemilik Order',
        'customer_email' => 'pemilik-order@example.com',
        'customer_phone' => '081234567890',
        'shipping_address' => 'Jl. Error 1, Jakarta',
        'total_amount' => 50000,
        'status' => 'paid',
    ]);

    $this->actingAs($otherBuyer)
        ->get("/{$store->slug}/orders/ERR-403-001/invoice")
        ->assertStatus(403)
        ->assertInertia(fn ($page) => $page
            ->component('Error')
            ->where('status', 403));
});
