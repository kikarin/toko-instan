<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\FirebaseAuthService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.midtrans.server_key' => 'SB-Mid-server-test',
        'services.midtrans.client_key' => 'SB-Mid-client-test',
    ]);

    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 'snap-token-sec',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/x',
        ], 201),
    ]);
});

function securityCheckoutContext(): array
{
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);
    $buyer = User::factory()->create([
        'role' => 'buyer',
        'email' => 'security-buyer@example.com',
        'name' => 'Buyer Aman',
        'store_id' => $store->id,
    ]);

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'name' => 'Produk Terpercaya',
        'sku' => 'SKU-SEC-001',
        'price' => 100000,
        'stock' => 20,
    ]);

    return compact('seller', 'tenant', 'store', 'buyer', 'product');
}

test('harga dari client diabaikan, harga diambil dari database', function () {
    ['store' => $store, 'buyer' => $buyer, 'product' => $product] = securityCheckoutContext();

    $this->actingAs($buyer)->post("/{$store->slug}/checkout", [
        'customer_name' => 'Buyer Aman',
        'customer_email' => $buyer->email,
        'customer_phone' => '081234567890',
        'shipping_address' => 'Jl. Merdeka 1, Jakarta',
        'items' => [
            ['id' => $product->id, 'qty' => 2, 'price' => 1],
        ],
    ])->assertRedirect();

    $order = Order::query()->latest('id')->first();

    expect($order)->not->toBeNull()
        // subtotal 200000 (dari DB) < 300000 → +15000 = 215000, bukan harga 1 dari client
        ->and((float) $order->total_amount)->toBe(215000.0);

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'price' => 100000,
        'qty' => 2,
        'total' => 200000,
    ]);
});

test('checkout ditolak jika item berasal dari toko yang berbeda', function () {
    ['store' => $store, 'buyer' => $buyer, 'product' => $product] = securityCheckoutContext();

    $otherSeller = User::factory()->create(['role' => 'seller']);
    $otherTenant = Tenant::factory()->create(['user_id' => $otherSeller->id]);
    $otherStore = Store::factory()->create(['tenant_id' => $otherTenant->id]);
    $otherProduct = Product::factory()->create([
        'store_id' => $otherStore->id,
        'name' => 'Produk Toko Lain',
        'price' => 50000,
    ]);

    $this->actingAs($buyer)->post("/{$store->slug}/checkout", [
        'customer_name' => 'Buyer Aman',
        'customer_email' => $buyer->email,
        'customer_phone' => '081234567890',
        'shipping_address' => 'Jl. Merdeka 1, Jakarta',
        'items' => [
            ['id' => $product->id, 'qty' => 1],
            ['id' => $otherProduct->id, 'qty' => 1],
        ],
    ])->assertSessionHasErrors('items');

    expect(Order::count())->toBe(0)
        ->and(OrderItem::count())->toBe(0);
});

test('checkout ditolak jika stok produk tidak mencukupi', function () {
    ['store' => $store, 'buyer' => $buyer, 'product' => $product] = securityCheckoutContext();
    $product->update(['stock' => 1]);

    $this->actingAs($buyer)->post("/{$store->slug}/checkout", [
        'customer_name' => 'Buyer Aman',
        'customer_email' => $buyer->email,
        'customer_phone' => '081234567890',
        'shipping_address' => 'Jl. Merdeka 1, Jakarta',
        'items' => [
            ['id' => $product->id, 'qty' => 5],
        ],
    ])->assertSessionHasErrors('items');

    expect(Order::count())->toBe(0);
});

test('seller tidak bisa mengubah status order milik toko lain', function () {
    $sellerA = User::factory()->create(['role' => 'seller']);
    $tenantA = Tenant::factory()->create(['user_id' => $sellerA->id]);
    $storeA = Store::factory()->create(['tenant_id' => $tenantA->id]);

    $sellerB = User::factory()->create(['role' => 'seller']);
    $tenantB = Tenant::factory()->create(['user_id' => $sellerB->id]);
    $storeB = Store::factory()->create(['tenant_id' => $tenantB->id]);

    $order = Order::factory()->create(['store_id' => $storeB->id, 'status' => 'pending']);

    $this->actingAs($sellerA)
        ->patch("/orders/{$order->id}/status", ['status' => 'paid'])
        ->assertForbidden();

    expect($order->fresh()->status)->toBe('pending');
});

test('seller bisa mengubah status order dari tokonya sendiri', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);

    $order = Order::factory()->create(['store_id' => $store->id, 'status' => 'pending']);

    $this->actingAs($seller)
        ->patch("/orders/{$order->id}/status", ['status' => 'processing'])
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('processing');
});

test('endpoint login dibatasi kecepatannya (throttle)', function () {
    $email = 'attacker-'.uniqid().'@example.com';

    foreach (range(1, 5) as $_) {
        $this->post('/login', [
            'email' => $email,
            'password' => 'salah-password',
        ]);
    }

    $this->post('/login', [
        'email' => $email,
        'password' => 'salah-password',
    ])->assertStatus(429);
});

test('google login menolak token yang tidak valid', function () {
    $this->post('/auth/google', ['id_token' => 'token-palsu'])
        ->assertSessionHasErrors('email');
});

test('google login membuat user dari token yang terverifikasi', function () {
    $this->mock(FirebaseAuthService::class, function ($mock) {
        $mock->shouldReceive('verifyIdToken')
            ->andReturn([
                'uid' => 'google-uid-1',
                'email' => 'google@example.com',
                'name' => 'Google User',
            ]);
    });

    $this->post('/auth/google', ['id_token' => 'token-valid'])
        ->assertRedirect();

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'google@example.com',
        'firebase_uid' => 'google-uid-1',
        'auth_provider' => 'google',
    ]);
});

test('seller tanpa toko melihat daftar order kosong, bukan toko lain', function () {
    $seller = User::factory()->create(['role' => 'seller']);

    $otherSeller = User::factory()->create(['role' => 'seller']);
    $otherTenant = Tenant::factory()->create(['user_id' => $otherSeller->id]);
    $otherStore = Store::factory()->create(['tenant_id' => $otherTenant->id]);
    Order::factory()->create(['store_id' => $otherStore->id]);

    $this->actingAs($seller)
        ->get('/orders')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('SellerOrders/Index')
            ->where('orders', [])
        );
});
