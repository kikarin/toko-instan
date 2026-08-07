<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\StockService;

use function Pest\Laravel\actingAs;

function stockSeller(): User
{
    return User::factory()->create(['role' => 'seller']);
}

function stockProduct(): Product
{
    $store = Store::factory()->create();
    $product = Product::factory()->create(['store_id' => $store->id, 'stock' => 10]);

    return $product;
}

it('seller dapat mencatat stok masuk', function () {
    $seller = stockSeller();
    $product = stockProduct();

    actingAs($seller)
        ->post("/inventory/{$product->id}/in", ['quantity' => 5, 'reason' => 'Restock'])
        ->assertRedirect();

    expect($product->fresh()->stock)->toBe(15);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'type' => 'in',
        'quantity' => 5,
        'stock_before' => 10,
        'stock_after' => 15,
        'reason' => 'Restock',
    ]);
});

it('seller dapat mencatat stok keluar', function () {
    $seller = stockSeller();
    $product = stockProduct();

    actingAs($seller)
        ->post("/inventory/{$product->id}/out", ['quantity' => 3])
        ->assertRedirect();

    expect($product->fresh()->stock)->toBe(7);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'type' => 'out',
        'quantity' => 3,
        'stock_before' => 10,
        'stock_after' => 7,
    ]);
});

it('menolak stok keluar melebihi stok tersedia', function () {
    $seller = stockSeller();
    $product = stockProduct();

    actingAs($seller)
        ->post("/inventory/{$product->id}/out", ['quantity' => 99])
        ->assertRedirect()
        ->assertSessionHasErrors('quantity');

    expect($product->fresh()->stock)->toBe(10);
});

it('seller dapat melakukan penyesuaian stok', function () {
    $seller = stockSeller();
    $product = stockProduct();

    actingAs($seller)
        ->post("/inventory/{$product->id}/adjust", ['new_stock' => 25, 'reason' => 'Penghitungan ulang'])
        ->assertRedirect();

    expect($product->fresh()->stock)->toBe(25);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'type' => 'in',
        'quantity' => 15,
        'stock_before' => 10,
        'stock_after' => 25,
    ]);
});

it('halaman inventory menampilkan daftar produk dan statistik', function () {
    $seller = stockSeller();
    $product = stockProduct();

    actingAs($seller)
        ->get('/inventory')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Inventory/Index')
            ->has('products', 1)
            ->where('products.0.id', $product->id)
            ->where('lowStockCount', 1));
});

it('riwayat stok menampilkan daftar movement', function () {
    $seller = stockSeller();
    $product = stockProduct();
    app(StockService::class)->stockIn($product, 5, 'Restock', $seller);

    actingAs($seller)
        ->get("/inventory/{$product->id}/history")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Inventory/History')
            ->has('movements', 1)
            ->where('movements.0.type', 'in'));
});
