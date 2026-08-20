<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('buyer can upload transfer proof and seller can confirm', function () {
    Storage::fake('r2');

    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id, 'slug' => 'toko-tf']);
    Wallet::factory()->create(['tenant_id' => $tenant->id]);

    $buyer = User::factory()->create([
        'role' => 'buyer',
        'store_id' => $store->id,
        'email' => 'buyer-tf@example.com',
    ]);

    $order = Order::factory()->create([
        'store_id' => $store->id,
        'customer_email' => $buyer->email,
        'order_number' => 'ORD-TF-1',
        'total_amount' => 75000,
        'status' => 'pending',
        'payment_method' => 'transfer',
    ]);

    $payment = Payment::factory()->manual()->create([
        'order_id' => $order->id,
        'tenant_id' => $tenant->id,
        'amount' => 75000,
        'status' => PaymentStatus::Pending,
        'method' => PaymentMethod::Transfer,
        'provider' => PaymentProvider::Manual,
    ]);

    $file = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

    $this->actingAs($buyer)
        ->post("/{$store->slug}/orders/{$order->order_number}/payment-proof", [
            'proof' => $file,
        ])
        ->assertRedirect();

    expect($payment->fresh()->status)->toBe(PaymentStatus::WaitingConfirmation)
        ->and($payment->fresh()->proof_path)->not->toBeNull();

    $this->actingAs($seller)
        ->post("/payments/{$payment->id}/confirm")
        ->assertRedirect();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Paid)
        ->and($order->fresh()->status)->toBe('paid');
});
