<?php

use App\Contracts\PaymentGateway;
use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Gateways\MidtransGateway;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.midtrans.server_key' => 'SB-Mid-server-test',
        'services.midtrans.client_key' => 'SB-Mid-client-test',
        'services.midtrans.is_production' => false,
        'services.payment.default' => 'midtrans',
    ]);
});

test('payment gateway interface binds to midtrans by default', function () {
    expect(app(PaymentGateway::class))->toBeInstanceOf(MidtransGateway::class);
});

test('checkout with qris creates midtrans payment snap token', function () {
    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 'snap-token-test',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-test',
        ], 201),
    ]);

    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);
    $buyer = User::factory()->create(['role' => 'buyer', 'store_id' => $store->id]);
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'price' => 50000,
        'stock' => 10,
        'is_active' => true,
    ]);

    $this->actingAs($buyer)->post("/{$store->slug}/checkout", [
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'customer_phone' => '08123456789',
        'shipping_address' => 'Jl. Test',
        'shipping_courier' => 'JNE Reguler',
        'payment_method' => 'qris',
        'items' => [['id' => $product->id, 'qty' => 1]],
    ])->assertRedirect();

    $order = Order::query()->latest('id')->first();
    expect($order->payment_method)->toBe('qris');

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'provider' => PaymentProvider::Midtrans->value,
        'method' => PaymentMethod::Qris->value,
        'snap_token' => 'snap-token-test',
        'status' => 'pending',
    ]);
});

test('payment service resolves gateways by method', function () {
    $service = app(PaymentService::class);

    expect($service->resolveGateway(PaymentMethod::Va))->toBeInstanceOf(MidtransGateway::class)
        ->and($service->resolveGateway(PaymentMethod::Transfer)->createPayment(
            Order::factory()->make(['total_amount' => 1000, 'order_number' => 'X']),
            PaymentMethod::Transfer,
        )->provider)->toBe('manual');
});
