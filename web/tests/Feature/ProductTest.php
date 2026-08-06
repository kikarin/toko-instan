<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\User;

/**
 * Create a primary store (used by the seller context) + a seller user.
 */
function sellerContext(): array
{
    $store = Store::factory()->create();
    $seller = User::factory()->state(['role' => 'seller'])->create();

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

test('seller can delete a product', function () {
    [$seller, $store] = sellerContext();
    $product = Product::factory()->create(['store_id' => $store->id]);

    $this->actingAs($seller)
        ->delete("/products/{$product->id}")
        ->assertRedirect(route('products.index'));

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});
