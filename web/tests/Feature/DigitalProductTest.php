<?php

use App\Actions\UploadDigitalProductFile;
use App\DTO\CreateOrderDTO;
use App\Enums\ProductType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('seller can create digital product with file path', function () {
    $seller = User::factory()->state(['role' => 'seller', 'store_id' => null])->create();
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    Store::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($seller)->post('/products', [
        'name' => 'E-Book Marketing',
        'category' => 'Digital',
        'price' => 49000,
        'stock' => 100,
        'is_active' => true,
        'type' => ProductType::Digital->value,
        'digital_file_path' => 'digital/2026/08/ebook.pdf',
        'digital_file_name' => 'ebook.pdf',
        'digital_file_mime' => 'application/pdf',
    ])->assertRedirect();

    $this->assertDatabaseHas('products', [
        'name' => 'E-Book Marketing',
        'type' => 'digital',
        'digital_file_path' => 'digital/2026/08/ebook.pdf',
    ]);
});

test('digital product requires file path', function () {
    $seller = User::factory()->state(['role' => 'seller', 'store_id' => null])->create();
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    Store::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($seller)->post('/products', [
        'name' => 'Broken Digital',
        'category' => 'Digital',
        'price' => 10000,
        'stock' => 10,
        'type' => 'digital',
    ])->assertSessionHasErrors('digital_file_path');
});

test('checkout of digital-only order has zero shipping', function () {
    $seller = User::factory()->state(['role' => 'seller', 'store_id' => null])->create();
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id, 'slug' => 'digital-store']);
    $product = Product::factory()->digital()->create([
        'store_id' => $store->id,
        'price' => 50000,
        'stock' => 5,
        'is_active' => true,
    ]);

    $buyer = User::factory()->state([
        'role' => 'buyer',
        'store_id' => $store->id,
        'email' => 'digital-buyer@example.com',
    ])->create();

    $dto = new CreateOrderDTO(
        customerName: $buyer->name,
        customerEmail: $buyer->email,
        customerPhone: '08123456789',
        shippingAddress: 'N/A',
        shippingCourier: 'Digital',
        paymentMethod: 'qris',
        items: [
            ['id' => $product->id, 'qty' => 1],
        ],
        notes: null,
    );

    $order = app(OrderService::class)->processCheckout($dto);

    expect((float) $order->total_amount)->toBe(50000.0);
    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_type' => 'digital',
        'digital_file_path' => $product->digital_file_path,
    ]);
});

test('buyer can download digital item after paid', function () {
    Storage::fake('r2');
    Storage::disk('r2')->put('digital/test/sample.pdf', 'pdf-content');

    $seller = User::factory()->state(['role' => 'seller', 'store_id' => null])->create();
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id, 'slug' => 'dl-store']);
    $product = Product::factory()->digital()->create([
        'store_id' => $store->id,
        'is_active' => true,
    ]);

    $buyer = User::factory()->state([
        'role' => 'buyer',
        'store_id' => $store->id,
        'email' => 'dl-buyer@example.com',
    ])->create();

    $order = Order::factory()->create([
        'store_id' => $store->id,
        'customer_email' => $buyer->email,
        'status' => 'paid',
    ]);

    $item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_type' => 'digital',
        'digital_file_path' => 'digital/test/sample.pdf',
        'digital_file_name' => 'sample.pdf',
    ]);

    $this->actingAs($buyer)
        ->get("/{$store->slug}/orders/items/{$item->id}/download")
        ->assertOk();
});

test('digital file upload action stores on r2', function () {
    Storage::fake('r2');

    $file = UploadedFile::fake()->create('guide.pdf', 200, 'application/pdf');
    $result = app(UploadDigitalProductFile::class)($file);

    expect($result['path'])->toContain('digital/')
        ->and($result['name'])->toBe('guide.pdf');

    Storage::disk('r2')->assertExists($result['path']);
});

test('digital upload endpoint accepts pdf under laravel max', function () {
    Storage::fake('r2');

    $seller = User::factory()->state(['role' => 'seller', 'store_id' => null])->create();
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    Store::factory()->create(['tenant_id' => $tenant->id]);

    $pdf = UploadedFile::fake()->createWithContent(
        'katalog.pdf',
        '%PDF-1.4 '.str_repeat('x', 4096)
    );

    $this->actingAs($seller)
        ->post('/uploads/digital', ['file' => $pdf])
        ->assertOk()
        ->assertJsonStructure(['path', 'name', 'mime', 'size']);
});
