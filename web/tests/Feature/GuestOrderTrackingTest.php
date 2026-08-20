<?php

use App\Mail\GuestOrderReceiptMail;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderTrackingService;
use Illuminate\Support\Facades\Mail;

function guestTrackingContext(): array
{
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id, 'slug' => 'toko-lacak']);

    $product = Product::factory()->create([
        'store_id' => $store->id,
        'price' => 50000,
        'stock' => 5,
    ]);

    return compact('seller', 'tenant', 'store', 'product');
}

test('guest checkout mengirim email receipt dengan link lacak', function () {
    Mail::fake();

    ['store' => $store, 'product' => $product] = guestTrackingContext();

    $this->post("/{$store->slug}/checkout", [
        'customer_name' => 'Tamu Email',
        'customer_email' => 'tamu-lacak@example.com',
        'customer_phone' => '08190000001',
        'shipping_address' => 'Jl. Lacak 99, Jakarta',
        'items' => [
            ['id' => $product->id, 'qty' => 1],
        ],
    ])->assertRedirect();

    Mail::assertQueued(GuestOrderReceiptMail::class, function (GuestOrderReceiptMail $mail) {
        return $mail->hasTo('tamu-lacak@example.com');
    });
});

test('signed tracking url membuka halaman status pesanan', function () {
    ['store' => $store] = guestTrackingContext();

    $order = Order::create([
        'store_id' => $store->id,
        'order_number' => 'ORD-LACAK-001',
        'customer_name' => 'Tamu Signed',
        'customer_email' => 'signed@example.com',
        'customer_phone' => '08190000002',
        'shipping_address' => 'Jl. Signed 1',
        'total_amount' => 50000,
        'status' => 'pending',
    ]);

    $url = app(OrderTrackingService::class)->signedUrl($order);

    $this->get($url)
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('OrderSuccess')
            ->where('invoice.order_number', 'ORD-LACAK-001'));
});

test('signed tracking url ditolak jika signature tidak valid', function () {
    ['store' => $store] = guestTrackingContext();

    $this->get("/{$store->slug}/orders/ORD-LACAK-002/track")
        ->assertForbidden();
});

test('guest dapat cek pesanan dengan nomor dan email yang cocok', function () {
    ['store' => $store] = guestTrackingContext();

    Order::create([
        'store_id' => $store->id,
        'order_number' => 'ORD-LOOKUP-001',
        'customer_name' => 'Tamu Lookup',
        'customer_email' => 'lookup@example.com',
        'customer_phone' => '08190000003',
        'shipping_address' => 'Jl. Lookup 1',
        'total_amount' => 75000,
        'status' => 'paid',
    ]);

    $response = $this->post("/{$store->slug}/cek-pesanan", [
        'order_number' => 'ORD-LOOKUP-001',
        'email' => 'lookup@example.com',
    ]);

    $response->assertRedirect();

    $location = (string) $response->headers->get('Location');

    expect($location)->toContain('/orders/ORD-LOOKUP-001/track')
        ->and($location)->toContain('signature=');
});

test('guest lookup ditolak jika email tidak cocok', function () {
    ['store' => $store] = guestTrackingContext();

    Order::create([
        'store_id' => $store->id,
        'order_number' => 'ORD-LOOKUP-002',
        'customer_name' => 'Tamu Lookup',
        'customer_email' => 'benar@example.com',
        'customer_phone' => '08190000004',
        'shipping_address' => 'Jl. Lookup 2',
        'total_amount' => 75000,
        'status' => 'paid',
    ]);

    $this->from("/{$store->slug}/cek-pesanan")
        ->post("/{$store->slug}/cek-pesanan", [
            'order_number' => 'ORD-LOOKUP-002',
            'email' => 'salah@example.com',
        ])
        ->assertSessionHasErrors('order_number');
});

test('halaman cek pesanan dapat diakses tamu', function () {
    ['store' => $store] = guestTrackingContext();

    $this->get("/{$store->slug}/cek-pesanan")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Orders/TrackLookup'));
});

test('order confirmation mail menyertakan signed tracking url', function () {
    ['store' => $store] = guestTrackingContext();

    $order = Order::create([
        'store_id' => $store->id,
        'order_number' => 'ORD-MAIL-001',
        'customer_name' => 'Tamu Mail',
        'customer_email' => 'mail@example.com',
        'customer_phone' => '08190000005',
        'shipping_address' => 'Jl. Mail 1',
        'total_amount' => 99000,
        'status' => 'paid',
    ]);

    $mail = new OrderConfirmationMail($order);
    $html = $mail->render();

    expect($html)->toContain('Lacak Pesanan')
        ->and($html)->toContain('/orders/ORD-MAIL-001/track')
        ->and($html)->toContain('signature=');
});

test('order shipped mail menyertakan signed tracking url', function () {
    ['store' => $store] = guestTrackingContext();

    $order = Order::create([
        'store_id' => $store->id,
        'order_number' => 'ORD-MAIL-002',
        'customer_name' => 'Tamu Kirim',
        'customer_email' => 'kirim@example.com',
        'customer_phone' => '08190000006',
        'shipping_address' => 'Jl. Kirim 1',
        'shipping_courier' => 'J&T Express',
        'tracking_number' => 'JT999',
        'total_amount' => 120000,
        'status' => 'shipped',
        'shipped_at' => now(),
    ]);

    $mail = new OrderShippedMail($order);
    $html = $mail->render();

    expect($html)->toContain('Lacak Pesanan')
        ->and($html)->toContain('/orders/ORD-MAIL-002/track')
        ->and($html)->toContain('signature=');
});
