<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;

function checkoutBuyerContext(): array
{
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);
    $buyer = User::factory()->create([
        'role' => 'buyer',
        'email' => 'buyer-order@example.com',
        'name' => 'Budi Buyer',
    ]);

    $productA = Product::factory()->create([
        'store_id' => $store->id,
        'name' => 'Kemeja Batik A',
        'sku' => 'SKU-A-001',
        'price' => 100000,
        'stock' => 20,
    ]);

    $productB = Product::factory()->create([
        'store_id' => $store->id,
        'name' => 'Celana Linen B',
        'sku' => 'SKU-B-002',
        'price' => 75000,
        'stock' => 15,
    ]);

    return compact('seller', 'tenant', 'store', 'buyer', 'productA', 'productB');
}

test('checkout creates order header and order_items snapshots', function () {
    ['buyer' => $buyer, 'store' => $store, 'tenant' => $tenant, 'productA' => $productA, 'productB' => $productB] = checkoutBuyerContext();

    $response = $this->actingAs($buyer)->post("/{$store->slug}/checkout", [
        'customer_name' => 'Budi Buyer',
        'customer_email' => $buyer->email,
        'customer_phone' => '08123456789',
        'shipping_address' => 'Jl. Merdeka 1, Jakarta',
        'shipping_courier' => 'JNE Reguler',
        'payment_method' => 'qris',
        'items' => [
            ['id' => $productA->id, 'name' => 'Kemeja Batik A', 'price' => 100000, 'qty' => 2],
            ['id' => $productB->id, 'name' => 'Celana Linen B', 'price' => 75000, 'qty' => 1],
        ],
        'notes' => 'Tolong packing rapi',
    ]);

    $order = Order::query()->latest('id')->first();

    expect($order)->not->toBeNull()
        ->and($order->store_id)->toBe($store->id)
        ->and($order->status)->toBe('pending')
        // subtotal 275000 < 300000 → +15000 shipping = 290000
        ->and((float) $order->total_amount)->toBe(290000.0);

    $response->assertRedirect(route('orders.success', ['store_slug' => $store->slug, 'orderNumber' => $order->order_number]));

    expect(OrderItem::where('order_id', $order->id)->count())->toBe(2);

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'tenant_id' => $tenant->id,
        'product_id' => $productA->id,
        'name' => 'Kemeja Batik A',
        'sku' => 'SKU-A-001',
        'qty' => 2,
        'price' => 100000,
        'total' => 200000,
    ]);

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_id' => $productB->id,
        'name' => 'Celana Linen B',
        'sku' => 'SKU-B-002',
        'qty' => 1,
        'price' => 75000,
        'total' => 75000,
    ]);

    expect($productA->fresh()->stock)->toBe(18)
        ->and($productB->fresh()->stock)->toBe(14);
});

test('seller orders page includes line items after checkout', function () {
    ['seller' => $seller, 'buyer' => $buyer, 'store' => $store, 'productA' => $productA, 'productB' => $productB] = checkoutBuyerContext();

    $this->actingAs($buyer)->post("/{$store->slug}/checkout", [
        'customer_name' => 'Budi Buyer',
        'customer_email' => $buyer->email,
        'customer_phone' => '08123456789',
        'shipping_address' => 'Jl. Merdeka 1, Jakarta',
        'items' => [
            ['id' => $productA->id, 'name' => 'Kemeja Batik A', 'price' => 100000, 'qty' => 2],
            ['id' => $productB->id, 'name' => 'Celana Linen B', 'price' => 75000, 'qty' => 1],
        ],
    ])->assertRedirect();

    $this->actingAs($seller)
        ->get('/orders')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('SellerOrders/Index')
            ->has('orders', 1)
            ->has('orders.0.items', 2)
            ->where('orders.0.items.0.product_name', 'Kemeja Batik A')
            ->where('orders.0.items.0.quantity', 2)
            ->where('orders.0.items.0.price', 100000)
            ->where('orders.0.items.0.subtotal', 200000)
            ->where('orders.0.items.1.product_name', 'Celana Linen B')
            ->where('orders.0.items.1.sku', 'SKU-B-002')
        );
});

test('buyer orders page includes line items after checkout', function () {
    ['buyer' => $buyer, 'store' => $store, 'productA' => $productA, 'productB' => $productB] = checkoutBuyerContext();

    $this->actingAs($buyer)->post("/{$store->slug}/checkout", [
        'customer_name' => 'Budi Buyer',
        'customer_email' => $buyer->email,
        'customer_phone' => '08123456789',
        'shipping_address' => 'Jl. Merdeka 1, Jakarta',
        'items' => [
            ['id' => $productA->id, 'name' => 'Kemeja Batik A', 'price' => 100000, 'qty' => 2],
            ['id' => $productB->id, 'name' => 'Celana Linen B', 'price' => 75000, 'qty' => 1],
        ],
    ])->assertRedirect();

    $this->actingAs($buyer)
        ->get("/{$store->slug}/orders")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Orders/Index')
            ->has('orders', 1)
            ->has('orders.0.items', 2)
            ->where('orders.0.items.0.product_name', 'Kemeja Batik A')
            ->where('orders.0.items.0.qty', 2)
            ->where('orders.0.items.1.product_name', 'Celana Linen B')
        );
});
