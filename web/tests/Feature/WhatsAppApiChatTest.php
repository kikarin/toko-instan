<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;

function premiumSeller(): array
{
    $seller = User::factory()->create(['role' => 'seller', 'phone' => '081234567890']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id, 'plan' => 'premium']);
    $store = Store::factory()->create(['tenant_id' => $tenant->id, 'phone' => '081234567890']);

    return [$seller, $tenant, $store];
}

test('whatsapp e164 normalizes indonesian numbers', function () {
    expect(app(WhatsAppService::class)->toE164('0812-3456-7890'))->toBe('6281234567890');
});

test('premium order notifies whatsapp', function () {
    config([
        'services.whatsapp.token' => 'wa-token',
        'services.whatsapp.phone_id' => '123',
        'services.midtrans.server_key' => 'SB-Mid-server-test',
    ]);
    Http::fake([
        'graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid']]], 200),
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 't',
            'redirect_url' => 'https://app.sandbox.midtrans.com/x',
        ], 201),
    ]);

    [, , $store] = premiumSeller();
    $buyer = User::factory()->create(['role' => 'buyer']);
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'price' => 25000,
        'stock' => 3,
        'is_active' => true,
        'type' => 'digital',
    ]);

    $this->actingAs($buyer)->post("/{$store->slug}/checkout", [
        'customer_name' => 'Budi',
        'customer_email' => $buyer->email,
        'customer_phone' => '0812',
        'shipping_address' => 'a',
        'payment_method' => 'qris',
        'items' => [['id' => $product->id, 'qty' => 1]],
    ])->assertRedirect();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/messages')
        && str_contains($request->url(), 'graph.facebook.com'));
});

test('store chat auto replies to the visitor', function () {
    $store = Store::factory()->create(['name' => 'Toko Tes Chat']);

    $this->postJson("/{$store->slug}/chat", ['body' => 'Masih ada stok?'])
        ->assertOk()
        ->assertJsonPath('messages.0.role', 'visitor')
        ->assertJsonPath('messages.1.role', 'assistant');

    expect($this->postJson("/{$store->slug}/chat", ['body' => 'Masih ada stok?'])->json('messages.1.body'))
        ->toContain('Toko Tes Chat');
});

test('api requires sanctum token', function () {
    $this->getJson('/api/products')->assertUnauthorized();
});

test('seller api can crud products and list orders', function () {
    [$seller, , $store] = premiumSeller();
    $token = $seller->createToken('ci')->plainTextToken;
    Order::factory()->create(['store_id' => $store->id, 'order_number' => 'INV-API-1']);

    $this->withToken($token)->getJson('/api/products')->assertOk()->assertJsonStructure(['data']);

    $created = $this->withToken($token)->postJson('/api/products', [
        'name' => 'API Sneaker',
        'category' => 'Fashion',
        'price' => 120000,
        'stock' => 4,
    ])->assertCreated()->json('data');

    $this->withToken($token)->getJson('/api/products/'.$created['id'])->assertOk()
        ->assertJsonPath('data.name', 'API Sneaker');

    $this->withToken($token)->putJson('/api/products/'.$created['id'], [
        'name' => 'API Sneaker V2',
        'category' => 'Fashion',
        'price' => 130000,
        'stock' => 3,
    ])->assertOk()->assertJsonPath('data.name', 'API Sneaker V2');

    $this->withToken($token)->getJson('/api/orders')->assertOk()
        ->assertJsonPath('data.0.order_number', 'INV-API-1');

    $this->withToken($token)->deleteJson('/api/products/'.$created['id'])->assertOk();
});

test('swagger docs are public', function () {
    $this->get('/api/docs')->assertOk();
    $this->get('/api/openapi.yaml')->assertOk()->assertSee('Toko Instan Public API', false);
});
