<?php

use App\Mail\OrderShippedMail;
use App\Models\Order;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function trackingContext(): array
{
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);

    $order = Order::create([
        'store_id' => $store->id,
        'order_number' => 'ORD-TRK-001',
        'customer_name' => 'Budi Lacak',
        'customer_email' => 'lacak@example.com',
        'customer_phone' => '08177000000',
        'shipping_address' => 'Jl. Lacak 1, Jakarta',
        'shipping_courier' => 'J&T Express',
        'total_amount' => 100000,
        'status' => 'processing',
    ]);

    return compact('seller', 'tenant', 'store', 'order');
}

test('seller dapat mengirim pesanan dengan nomor resi dan email resi terkirim', function () {
    Mail::fake();

    ['seller' => $seller, 'order' => $order] = trackingContext();

    $this->actingAs($seller)
        ->patch("/orders/{$order->id}/status", [
            'status' => 'shipped',
            'tracking_number' => 'JT1234567890',
            'tracking_courier' => 'J&T Express',
        ])
        ->assertRedirect();

    $order->refresh();

    expect($order->status)->toBe('shipped')
        ->and($order->tracking_number)->toBe('JT1234567890')
        ->and($order->tracking_courier)->toBe('J&T Express')
        ->and($order->shipped_at)->not->toBeNull();

    Mail::assertQueued(OrderShippedMail::class, function (OrderShippedMail $mail) {
        return $mail->hasTo('lacak@example.com') && $mail->order->tracking_number === 'JT1234567890';
    });
});

test('invoice pesanan menyertakan nomor resi dan link lacak', function () {
    ['seller' => $seller, 'store' => $store, 'order' => $order] = trackingContext();

    $order->update([
        'status' => 'shipped',
        'tracking_number' => 'JT1234567890',
        'tracking_courier' => 'J&T Express',
        'shipped_at' => now(),
    ]);

    $this->actingAs($seller)
        ->get("/orders/{$order->order_number}/invoice")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Order/Invoice')
            ->where('invoice.tracking_number', 'JT1234567890')
            ->where('invoice.tracking_courier', 'J&T Express')
            ->where('invoice.tracking_url', 'https://www.jet.co.id/track/trace?no=JT1234567890'));
});

test('halaman riwayat pembeli menampilkan nomor resi', function () {
    ['seller' => $seller, 'store' => $store, 'order' => $order] = trackingContext();

    $buyer = User::factory()->create(['role' => 'buyer', 'email' => 'lacak@example.com', 'store_id' => $store->id]);

    $order->update([
        'status' => 'shipped',
        'tracking_number' => 'JT1234567890',
        'tracking_courier' => 'J&T Express',
        'shipped_at' => now(),
    ]);

    $this->actingAs($buyer)
        ->get("/{$store->slug}/orders")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Orders/Index')
            ->where('orders.0.tracking_number', 'JT1234567890')
            ->where('orders.0.tracking_url', 'https://www.jet.co.id/track/trace?no=JT1234567890'));
});
