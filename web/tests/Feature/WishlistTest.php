<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\User;

use function Pest\Laravel\actingAs;

function wishlistBuyer(): User
{
    return User::factory()->create(['role' => 'buyer']);
}

function wishlistProduct(): Product
{
    $store = Store::factory()->create();
    $product = Product::factory()->create(['store_id' => $store->id]);

    return $product;
}

it('menampilkan daftar wishlist kosong untuk buyer', function () {
    $buyer = wishlistBuyer();

    actingAs($buyer)
        ->get('/wishlist')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Wishlist')
            ->where('products', []));
});

it('buyer dapat menambahkan produk ke wishlist', function () {
    $buyer = wishlistBuyer();
    $product = wishlistProduct();

    actingAs($buyer)
        ->post("/wishlist/{$product->id}")
        ->assertOk()
        ->assertJson(['added' => true, 'count' => 1]);

    $this->assertDatabaseHas('wishlists', [
        'user_id' => $buyer->id,
        'product_id' => $product->id,
    ]);
});

it('toggle wishlist menghapus produk jika sudah ada', function () {
    $buyer = wishlistBuyer();
    $product = wishlistProduct();
    $buyer->wishlistProducts()->attach($product->id);

    actingAs($buyer)
        ->post("/wishlist/{$product->id}")
        ->assertOk()
        ->assertJson(['added' => false, 'count' => 0]);

    $this->assertDatabaseMissing('wishlists', [
        'user_id' => $buyer->id,
        'product_id' => $product->id,
    ]);
});

it('buyer dapat menghapus produk dari wishlist', function () {
    $buyer = wishlistBuyer();
    $product = wishlistProduct();
    $buyer->wishlistProducts()->attach($product->id);

    actingAs($buyer)
        ->delete("/wishlist/{$product->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('wishlists', [
        'user_id' => $buyer->id,
        'product_id' => $product->id,
    ]);
});

it('menampilkan produk wishlist yang tersimpan', function () {
    $buyer = wishlistBuyer();
    $product = wishlistProduct();
    $buyer->wishlistProducts()->attach($product->id);

    actingAs($buyer)
        ->get('/wishlist')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Wishlist')
            ->has('products', 1)
            ->where('products.0.id', $product->id));
});
