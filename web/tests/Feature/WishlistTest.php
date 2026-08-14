<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\User;

use function Pest\Laravel\actingAs;

function wishlistContext(): array
{
    $store = Store::factory()->create();
    $buyer = User::factory()->create(['role' => 'buyer', 'store_id' => $store->id]);
    $product = Product::factory()->create(['store_id' => $store->id]);

    return compact('buyer', 'store', 'product');
}

it('menampilkan daftar wishlist kosong untuk buyer', function () {
    ['buyer' => $buyer, 'store' => $store] = wishlistContext();

    actingAs($buyer)
        ->get("/{$store->slug}/wishlist")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Wishlist')
            ->where('products', []));
});

it('buyer dapat menambahkan produk ke wishlist', function () {
    ['buyer' => $buyer, 'store' => $store, 'product' => $product] = wishlistContext();

    actingAs($buyer)
        ->post("/{$store->slug}/wishlist/{$product->id}")
        ->assertOk()
        ->assertJson(['added' => true, 'count' => 1]);

    $this->assertDatabaseHas('wishlists', [
        'user_id' => $buyer->id,
        'product_id' => $product->id,
    ]);
});

it('toggle wishlist menghapus produk jika sudah ada', function () {
    ['buyer' => $buyer, 'store' => $store, 'product' => $product] = wishlistContext();
    $buyer->wishlistProducts()->attach($product->id);

    actingAs($buyer)
        ->post("/{$store->slug}/wishlist/{$product->id}")
        ->assertOk()
        ->assertJson(['added' => false, 'count' => 0]);

    $this->assertDatabaseMissing('wishlists', [
        'user_id' => $buyer->id,
        'product_id' => $product->id,
    ]);
});

it('buyer dapat menghapus produk dari wishlist', function () {
    ['buyer' => $buyer, 'store' => $store, 'product' => $product] = wishlistContext();
    $buyer->wishlistProducts()->attach($product->id);

    actingAs($buyer)
        ->delete("/{$store->slug}/wishlist/{$product->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('wishlists', [
        'user_id' => $buyer->id,
        'product_id' => $product->id,
    ]);
});

it('menampilkan produk wishlist yang tersimpan', function () {
    ['buyer' => $buyer, 'store' => $store, 'product' => $product] = wishlistContext();
    $buyer->wishlistProducts()->attach($product->id);

    actingAs($buyer)
        ->get("/{$store->slug}/wishlist")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Wishlist')
            ->has('products', 1)
            ->where('products.0.id', $product->id));
});
