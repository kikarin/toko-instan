<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;

/**
 * Create a store owned by the seller (via tenant) + a seller user.
 */
function sellerContext(): array
{
    $seller = User::factory()->state(['role' => 'seller'])->create();
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);

    return [$seller, $store];
}

test('guest is redirected away from products', function () {
    $this->get('/products')->assertRedirect('/login');
});

test('seller is redirected from buyer marketplace products', function () {
    $buyer = User::factory()->state(['role' => 'buyer'])->create();

    $this->actingAs($buyer)->get('/products')->assertRedirect('/marketplace');
});

test('seller can view the products of their primary store', function () {
    [$seller, $store] = sellerContext();
    Product::factory()->create(['store_id' => $store->id]);
    Product::factory()->create(['store_id' => $store->id]);

    $this->actingAs($seller)
        ->get('/products')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Products/Index'))
        ->assertInertia(fn ($page) => $page->has('products', 2));
});

test('seller can create a product', function () {
    [$seller] = sellerContext();

    $this->actingAs($seller)->post('/products', [
        'name' => 'Kemeja Batik Premium',
        'category' => 'Fashion',
        'price' => 285000,
        'stock' => 50,
        'tag' => 'Bestseller',
        'img' => 'https://example.com/img.jpg',
    ])->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', [
        'name' => 'Kemeja Batik Premium',
        'category' => 'Fashion',
        'stock' => 50,
    ]);
});

test('seller can update a product', function () {
    [$seller, $store] = sellerContext();
    $product = Product::factory()->create(['store_id' => $store->id]);

    $this->actingAs($seller)->put("/products/{$product->id}", [
        'name' => 'Nama Baru',
        'category' => 'Kuliner',
        'price' => 150000,
        'stock' => 20,
        'tag' => null,
        'img' => null,
    ])->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Nama Baru',
        'category' => 'Kuliner',
        'price' => 150000,
        'stock' => 20,
    ]);
});

test('seller can delete a product (soft delete)', function () {
    [$seller, $store] = sellerContext();
    $product = Product::factory()->create(['store_id' => $store->id]);

    $this->actingAs($seller)
        ->delete("/products/{$product->id}")
        ->assertRedirect(route('products.index'));

    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

test('seller can update product stock', function () {
    [$seller, $store] = sellerContext();
    $product = Product::factory()->create(['store_id' => $store->id, 'stock' => 10]);

    $this->actingAs($seller)
        ->patch("/products/{$product->id}/stock", ['stock' => 25])
        ->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 25]);
});

test('seller can toggle product active status', function () {
    [$seller, $store] = sellerContext();
    $product = Product::factory()->create(['store_id' => $store->id, 'is_active' => true]);

    $this->actingAs($seller)
        ->post("/products/{$product->id}/toggle-active")
        ->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => false]);
});

test('inactive products are hidden from the marketplace catalog', function () {
    [$seller, $store] = sellerContext();
    Product::factory()->create(['store_id' => $store->id, 'is_active' => true]);
    Product::factory()->create(['store_id' => $store->id, 'is_active' => false]);

    $buyer = User::factory()->state(['role' => 'buyer'])->create();

    $this->actingAs($buyer)
        ->get('/marketplace')
        ->assertInertia(fn ($page) => $page->component('Marketplace'))
        ->assertInertia(fn ($page) => $page->has('products', 1));
});

test('marketplace only shows products from the seller own store', function () {
    $seller = User::factory()->state(['role' => 'seller'])->create();
    $sellerTenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $sellerStore = Store::factory()->create(['tenant_id' => $sellerTenant->id]);
    $ownedProduct = Product::factory()->create(['store_id' => $sellerStore->id, 'is_active' => true]);

    $otherStore = Store::factory()->create();
    Product::factory()->create(['store_id' => $otherStore->id, 'is_active' => true]);

    $this->actingAs($seller)
        ->get('/marketplace')
        ->assertInertia(fn ($page) => $page->has('products', 1))
        ->assertInertia(fn ($page) => $page->where('products.0.id', $ownedProduct->id));
});

test('seller can only manage products from their own store', function () {
    [$seller, $store] = sellerContext();
    $otherStore = Store::factory()->create();
    $foreignProduct = Product::factory()->create(['store_id' => $otherStore->id]);

    $this->actingAs($seller)
        ->get("/products/{$foreignProduct->id}/edit")
        ->assertNotFound();

    $this->actingAs($seller)
        ->delete("/products/{$foreignProduct->id}")
        ->assertNotFound();
});
