<?php

use App\Mail\OrderCreatedMail;
use App\Mail\WithdrawalApprovedMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreVisit;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Services\StoreCmsService;
use App\Services\WithdrawService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

test('cms themes include fashion and food variants', function () {
    $themes = app(StoreCmsService::class)->themes();

    expect($themes)->toHaveKeys(['modern', 'fashion', 'food'])
        ->and($themes['fashion']['variant'])->toBe('fashion')
        ->and($themes['food']['font'])->toBe('Nunito');
});

test('public storefront records a unique daily visit', function () {
    $store = Store::factory()->create();

    $this->get("/{$store->slug}")->assertOk();

    expect(StoreVisit::query()->where('store_id', $store->id)->count())->toBe(1);
});

test('seller dashboard includes analytics charts', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);

    Order::factory()->create([
        'store_id' => $store->id,
        'status' => 'paid',
        'total_amount' => 50000,
    ]);

    $this->actingAs($seller)->get('/dashboard')->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('charts.Hari.labels')
            ->has('charts.Bulan.revenue'));
});

test('new order emails the seller', function () {
    Mail::fake();
    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 't',
            'redirect_url' => 'https://app.sandbox.midtrans.com/x',
        ], 201),
    ]);
    config(['services.midtrans.server_key' => 'SB-Mid-server-test']);

    $seller = User::factory()->create(['role' => 'seller', 'email' => 'seller-alert@example.com']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id, 'is_pkp' => false]);
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

    Mail::assertQueued(OrderCreatedMail::class);
    expect($seller->notifications()->count())->toBe(1);
});

test('approving withdrawal emails the seller', function () {
    Mail::fake();
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $wallet = Wallet::factory()->create(['tenant_id' => $tenant->id]);
    $withdrawal = Withdrawal::factory()->create([
        'tenant_id' => $tenant->id,
        'wallet_id' => $wallet->id,
        'store_id' => Store::factory()->create(['tenant_id' => $tenant->id])->id,
        'status' => 'pending',
        'net_amount' => 10000,
        'account_name' => 'Budi',
    ]);

    app(WithdrawService::class)->approve($withdrawal);

    Mail::assertQueued(WithdrawalApprovedMail::class);
});
