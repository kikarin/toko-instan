<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function reviewContext(): array
{
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);
    $buyer = User::factory()->create(['role' => 'buyer', 'email' => 'reviewer@example.com']);
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'slug' => 'baju-review',
        'rating' => 0,
    ]);

    $order = Order::create([
        'store_id' => $store->id,
        'order_number' => 'ORD-REV-001',
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'customer_phone' => '0812',
        'shipping_address' => 'Jl. A',
        'total_amount' => 50000,
        'status' => 'completed',
    ]);

    $item = $order->items()->create([
        'tenant_id' => $tenant->id,
        'product_id' => $product->id,
        'name' => $product->name,
        'sku' => 'SKU-R',
        'price' => 50000,
        'qty' => 1,
        'total' => 50000,
    ]);

    return compact('seller', 'store', 'buyer', 'product', 'order', 'item');
}

test('buyer can review purchased product with rating and photo', function () {
    Storage::fake('r2');
    ['buyer' => $buyer, 'store' => $store, 'product' => $product, 'item' => $item] = reviewContext();

    $this->actingAs($buyer)->post("/{$store->slug}/p/{$product->slug}/reviews", [
        'rating' => 5,
        'body' => 'Bagus sekali',
        'order_item_id' => $item->id,
        'photo' => UploadedFile::fake()->image('review.jpg', 200, 200),
    ])->assertRedirect();

    expect(Review::query()->count())->toBe(1)
        ->and(Review::query()->first()->rating)->toBe(5)
        ->and(Review::query()->first()->photo_path)->not->toBeNull()
        ->and((float) $product->fresh()->rating)->toBe(5.0);
});

test('buyer cannot review product they did not buy', function () {
    ['store' => $store, 'product' => $product] = reviewContext();
    $other = User::factory()->create(['role' => 'buyer', 'email' => 'lain@example.com']);

    $this->actingAs($other)->post("/{$store->slug}/p/{$product->slug}/reviews", [
        'rating' => 4,
        'body' => 'Hmm',
    ])->assertSessionHasErrors();

    expect(Review::query()->count())->toBe(0);
});
