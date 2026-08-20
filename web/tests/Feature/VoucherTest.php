<?php

use App\Enums\VoucherType;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Facades\Http;

function promoContext(): array
{
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id, 'is_pkp' => false]);
    $buyer = User::factory()->create(['role' => 'buyer']);
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'price' => 100000,
        'stock' => 10,
        'is_active' => true,
    ]);

    return compact('seller', 'tenant', 'store', 'buyer', 'product');
}

test('seller can create percent and nominal vouchers', function () {
    ['seller' => $seller] = promoContext();

    $this->actingAs($seller)->post('/vouchers', [
        'code' => 'hemat10',
        'name' => 'Hemat 10rb',
        'type' => 'nominal',
        'value' => 10000,
        'is_active' => true,
    ])->assertRedirect();

    $this->actingAs($seller)->post('/vouchers', [
        'code' => 'DISC10',
        'name' => 'Diskon 10%',
        'type' => 'percent',
        'value' => 10,
        'max_discount' => 20000,
        'is_active' => true,
    ])->assertRedirect();

    expect(Voucher::query()->count())->toBe(2)
        ->and(Voucher::query()->where('code', 'HEMAT10')->first()?->type)->toBe(VoucherType::Nominal);
});

test('checkout applies voucher discount', function () {
    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 'snap-voucher',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/x',
        ], 201),
    ]);
    config(['services.midtrans.server_key' => 'SB-Mid-server-test']);

    ['buyer' => $buyer, 'store' => $store, 'product' => $product] = promoContext();

    Voucher::factory()->create([
        'store_id' => $store->id,
        'tenant_id' => $store->tenant_id,
        'code' => 'HEMAT10',
        'type' => VoucherType::Nominal,
        'value' => 10000,
        'min_spend' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($buyer)->post("/{$store->slug}/checkout", [
        'customer_name' => 'Budi',
        'customer_email' => $buyer->email,
        'customer_phone' => '08123456789',
        'shipping_address' => 'Jl. Test, Jakarta',
        'destination_city' => 'Jakarta',
        'payment_method' => 'qris',
        'voucher_code' => 'HEMAT10',
        'items' => [['id' => $product->id, 'qty' => 1]],
    ])->assertRedirect();

    $order = Order::query()->latest('id')->first();

    expect((int) $order->discount)->toBe(10000)
        ->and((int) $order->tax)->toBe(0)
        ->and((float) $order->total_amount)->toBe(100000.0 - 10000 + (int) $order->shipping_cost)
        ->and($order->voucher_code)->toBe('HEMAT10');
});
