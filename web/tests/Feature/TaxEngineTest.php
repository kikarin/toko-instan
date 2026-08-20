<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Http;

function taxContext(bool $pkp = true): array
{
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create([
        'tenant_id' => $tenant->id,
        'is_pkp' => $pkp,
        'npwp' => '01.234.567.8-901.000',
        'nik' => '3171012345670001',
        'tax_name' => 'PT Pajak Test',
        'tax_address' => 'Jl. Pajak 1',
    ]);
    $buyer = User::factory()->create(['role' => 'buyer']);
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'price' => 100000,
        'stock' => 5,
        'is_active' => true,
        'type' => 'digital',
    ]);

    return compact('seller', 'tenant', 'store', 'buyer', 'product');
}

test('seller can update tax profile', function () {
    ['seller' => $seller, 'store' => $store] = taxContext(false);

    $this->actingAs($seller)->put('/store-settings', [
        'name' => $store->name,
        'slug' => $store->slug,
        'is_pkp' => true,
        'npwp' => '10.0.0.1-000.000',
        'nik' => '1234567890123456',
        'tax_name' => 'CV Baru',
        'tax_address' => 'Jl. Baru',
    ])->assertRedirect();

    $store->refresh();

    expect($store->is_pkp)->toBeTrue()
        ->and($store->npwp)->toBe('10.0.0.1-000.000')
        ->and($store->tax_name)->toBe('CV Baru');
});

test('pkp store adds 11 percent ppn on checkout', function () {
    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 'snap-tax',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/x',
        ], 201),
    ]);
    config(['services.midtrans.server_key' => 'SB-Mid-server-test', 'tax.ppn_rate' => 11]);

    ['buyer' => $buyer, 'store' => $store, 'product' => $product] = taxContext(true);

    $this->actingAs($buyer)->post("/{$store->slug}/checkout", [
        'customer_name' => 'Budi',
        'customer_email' => $buyer->email,
        'customer_phone' => '08123456789',
        'shipping_address' => 'digital',
        'payment_method' => 'qris',
        'items' => [['id' => $product->id, 'qty' => 1]],
    ])->assertRedirect();

    $order = Order::query()->latest('id')->first();

    expect((int) $order->tax)->toBe(11000)
        ->and((float) $order->total_amount)->toBe(111000.0);
});

test('seller tax report exports csv and excel', function () {
    ['seller' => $seller, 'store' => $store, 'buyer' => $buyer] = taxContext(true);

    Order::create([
        'store_id' => $store->id,
        'order_number' => 'ORD-TAX-1',
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'customer_phone' => '0812',
        'shipping_address' => 'Jl. A',
        'total_amount' => 111000,
        'tax' => 11000,
        'discount' => 0,
        'shipping_cost' => 0,
        'status' => 'paid',
    ]);

    $this->actingAs($seller)->get('/tax-reports')->assertOk()
        ->assertInertia(fn ($page) => $page->component('TaxReports/Index')->has('report.rows', 1));

    $this->actingAs($seller)->get('/tax-reports/export?format=csv&year='.now()->year)
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $this->actingAs($seller)->get('/tax-reports/export?format=xls&year='.now()->year)
        ->assertOk();
});
