<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;

test('seller dapat mengakses halaman daftar pelanggan dan melihat rekap data pelanggan', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Toko Pelanggan Kami',
    ]);

    $customer = Customer::create([
        'store_id' => $store->id,
        'name' => 'Budi Pelanggan',
        'email' => 'budi@example.com',
        'phone' => '081234567890',
    ]);

    Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'order_number' => 'ORD-CUST-001',
        'customer_name' => 'Budi Pelanggan',
        'customer_email' => 'budi@example.com',
        'customer_phone' => '081234567890',
        'shipping_address' => 'Jl. Merdeka No. 1',
        'total_amount' => 100000,
        'status' => 'completed',
    ]);

    Order::create([
        'store_id' => $store->id,
        'customer_id' => $customer->id,
        'order_number' => 'ORD-CUST-002',
        'customer_name' => 'Budi Pelanggan',
        'customer_email' => 'budi@example.com',
        'customer_phone' => '081234567890',
        'shipping_address' => 'Jl. Merdeka No. 1',
        'total_amount' => 150000,
        'status' => 'processing',
    ]);

    $this->actingAs($seller)
        ->get('/customers')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Customers/Index')
            ->where('storeName', 'Toko Pelanggan Kami')
            ->has('customers', 1)
            ->where('customers.0.name', 'Budi Pelanggan')
            ->where('customers.0.email', 'budi@example.com')
            ->where('customers.0.total_orders', 2)
            ->where('customers.0.total_spent', 250000)
        );
});

test('buyer tidak dapat mengakses halaman pelanggan seller', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    $this->actingAs($buyer)
        ->get('/customers')
        ->assertRedirect('/');
});
