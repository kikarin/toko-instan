<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ShippingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

test('shipping quote returns courier rates for a destination city', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id, 'slug' => 'toko-ongkir']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'price' => 50000,
        'stock' => 10,
        'weight_gram' => 500,
    ]);

    $this->actingAs($buyer)
        ->postJson("/{$store->slug}/shipping/quote", [
            'destination_city' => 'KOTA BOGOR',
            'destination_postal_code' => 16146,
            'items' => [['id' => (string) $product->id, 'qty' => '1']],
        ])
        ->assertOk()
        ->assertJsonStructure(['rates' => [['id', 'courier', 'cost']]]);
});

test('checkout uses quoted shipping cost in order total', function () {
    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 'snap-token-ship',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/x',
        ], 201),
    ]);
    config(['services.midtrans.server_key' => 'SB-Mid-server-test']);

    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);
    $buyer = User::factory()->create(['role' => 'buyer']);
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'price' => 100000,
        'stock' => 5,
        'weight_gram' => 500,
        'is_active' => true,
    ]);

    $quote = $this->actingAs($buyer)
        ->postJson("/{$store->slug}/shipping/quote", [
            'destination_city' => 'Surabaya',
            'items' => [['id' => $product->id, 'qty' => 1]],
        ])
        ->json('rates.0');

    $this->actingAs($buyer)->post("/{$store->slug}/checkout", [
        'customer_name' => 'Budi',
        'customer_email' => $buyer->email,
        'customer_phone' => '08123456789',
        'shipping_address' => 'Jl. Test, Surabaya',
        'destination_city' => 'Surabaya',
        'shipping_rate_id' => $quote['id'],
        'shipping_courier' => $quote['name'],
        'shipping_service' => $quote['service'],
        'shipping_cost' => $quote['cost'],
        'payment_method' => 'qris',
        'items' => [['id' => $product->id, 'qty' => 1]],
    ])->assertRedirect();

    $order = Order::query()->latest('id')->first();

    expect((int) $order->shipping_cost)->toBe((int) $quote['cost'])
        ->and((float) $order->total_amount)->toBe(100000.0 + (float) $quote['cost']);
});

test('seller can move order packed then shipped with tracking number', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);
    $order = Order::factory()->create([
        'store_id' => $store->id,
        'status' => 'processing',
        'total_amount' => 50000,
    ]);

    $this->actingAs($seller)
        ->patch("/orders/{$order->id}/status", ['status' => 'packed'])
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('packed')
        ->and($order->fresh()->packed_at)->not->toBeNull();

    $this->actingAs($seller)
        ->patch("/orders/{$order->id}/status", [
            'status' => 'shipped',
            'tracking_number' => 'JNE123456789',
        ])
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('shipped')
        ->and($order->fresh()->tracking_number)->toBe('JNE123456789')
        ->and($order->fresh()->shipped_at)->not->toBeNull();
});

test('shipped status requires tracking number', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);
    $order = Order::factory()->create([
        'store_id' => $store->id,
        'status' => 'packed',
    ]);

    $this->actingAs($seller)
        ->patch("/orders/{$order->id}/status", ['status' => 'shipped'])
        ->assertSessionHasErrors('tracking_number');
});

test('rajaongkir gateway is used when api key is set', function () {
    Cache::flush();
    config([
        'services.rajaongkir.key' => 'test-key',
        'services.rajaongkir.base_url' => 'https://api.rajaongkir.com/starter',
        'services.shipping.couriers' => 'jne',
    ]);

    Http::fake([
        'api.rajaongkir.com/starter/city' => Http::response([
            'rajaongkir' => [
                'results' => [
                    ['city_id' => '153', 'city_name' => 'Jakarta Selatan'],
                    ['city_id' => '23', 'city_name' => 'Bandung'],
                ],
            ],
        ], 200),
        'api.rajaongkir.com/starter/cost' => Http::response([
            'rajaongkir' => [
                'results' => [[
                    'code' => 'jne',
                    'name' => 'Jalur Nugraha Ekakurir',
                    'costs' => [[
                        'service' => 'REG',
                        'description' => 'Layanan Reguler',
                        'cost' => [['value' => 21000, 'etd' => '2-3']],
                    ]],
                ]],
            ],
        ], 200),
    ]);

    $store = Store::factory()->create(['origin_city' => 'Jakarta Selatan']);
    $product = Product::factory()->create(['store_id' => $store->id, 'weight_gram' => 1000]);

    $rates = app(ShippingService::class)->quoteForStore(
        $store,
        'Bandung',
        [['id' => $product->id, 'qty' => 1]],
    );

    expect($rates[0]->cost)->toBe(21000)
        ->and($rates[0]->courier)->toBe('jne');
});
