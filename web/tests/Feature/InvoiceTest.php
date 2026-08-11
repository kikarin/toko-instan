<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;

function invoiceContext(): array
{
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Toko Uji Invoice',
    ]);

    $buyer = User::factory()->create([
        'role' => 'buyer',
        'email' => 'invoice-buyer@example.com',
        'name' => 'Budi Invoice',
    ]);

    $otherBuyer = User::factory()->create([
        'role' => 'buyer',
        'email' => 'orang-lain@example.com',
    ]);

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'name' => 'Sapu Ijuk Premium',
        'sku' => 'SKU-INV-001',
        'price' => 50000,
        'stock' => 10,
    ]);

    $order = Order::create([
        'store_id' => $store->id,
        'order_number' => 'INV-TEST-001',
        'customer_name' => 'Budi Invoice',
        'customer_email' => 'invoice-buyer@example.com',
        'customer_phone' => '081299887766',
        'shipping_address' => 'Jl. Invoice 12, Bandung',
        'total_amount' => 65000,
        'status' => 'paid',
        'notes' => 'Mohon dicek paketnya',
    ]);

    $order->items()->create([
        'tenant_id' => $tenant->id,
        'product_id' => $product->id,
        'name' => 'Sapu Ijuk Premium',
        'sku' => 'SKU-INV-001',
        'price' => 50000,
        'qty' => 1,
        'total' => 50000,
    ]);

    return compact('seller', 'tenant', 'store', 'buyer', 'otherBuyer', 'order');
}

it('buyer dapat membuka invoice pesanan miliknya dengan rincian lengkap', function () {
    ['buyer' => $buyer, 'store' => $store] = invoiceContext();

    $this->actingAs($buyer)
        ->get("/{$store->slug}/orders/INV-TEST-001/invoice")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Order/Invoice')
            ->has('invoice')
            ->where('invoice.order_number', 'INV-TEST-001')
            ->where('invoice.subtotal', 50000)
            ->where('invoice.shipping_fee', 15000)
            ->where('invoice.total_amount', 'Rp 65.000')
            ->where('invoice.shipping_address', 'Jl. Invoice 12, Bandung')
            ->where('invoice.notes', 'Mohon dicek paketnya')
            ->has('invoice.items', 1)
            ->where('invoice.items.0.product_name', 'Sapu Ijuk Premium')
            ->where('invoice.items.0.qty', 1));
});

it('buyer lain tidak bisa membuka invoice orang lain', function () {
    ['otherBuyer' => $otherBuyer, 'store' => $store] = invoiceContext();

    $this->actingAs($otherBuyer)
        ->get("/{$store->slug}/orders/INV-TEST-001/invoice")
        ->assertForbidden();
});

it('seller dapat membuka invoice order dari tokonya', function () {
    ['seller' => $seller, 'buyer' => $buyer, 'store' => $store] = invoiceContext();

    $this->actingAs($buyer)->get("/{$store->slug}/orders/INV-TEST-001/invoice")->assertOk();

    $this->actingAs($seller)
        ->get('/orders/INV-TEST-001/invoice')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Order/Invoice')
            ->where('invoice.store_name', 'Toko Uji Invoice'));
});

it('seller menolak akses invoice order tokok orang lain', function () {
    $foreignSeller = User::factory()->create(['role' => 'seller']);
    $foreignTenant = Tenant::factory()->create(['user_id' => $foreignSeller->id]);
    Store::factory()->create(['tenant_id' => $foreignTenant->id]);

    invoiceContext();

    $this->actingAs($foreignSeller)
        ->get('/orders/INV-TEST-001/invoice')
        ->assertForbidden();
});

it('invoice mengembalikan 404 untuk nomor pesanan yang tidak diketahui', function () {
    ['buyer' => $buyer, 'store' => $store] = invoiceContext();

    $this->actingAs($buyer)
        ->get("/{$store->slug}/orders/ORD-TIDAK-ADA/invoice")
        ->assertNotFound();
});
