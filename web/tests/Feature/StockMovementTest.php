<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\StockService;

use function Pest\Laravel\actingAs;

function stockSeller(): array
{
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);

    return [$seller, $store];
}

function stockProduct(Store $store): Product
{
    return Product::factory()->create(['store_id' => $store->id, 'stock' => 10]);
}

it('seller dapat mencatat stok masuk', function () {
    [$seller, $store] = stockSeller();
    $product = stockProduct($store);

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
    [$seller, $store] = stockSeller();
    $product = stockProduct($store);

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
    [$seller, $store] = stockSeller();
    $product = stockProduct($store);

    actingAs($seller)
        ->post("/inventory/{$product->id}/out", ['quantity' => 99])
        ->assertRedirect()
        ->assertSessionHasErrors('quantity');

    expect($product->fresh()->stock)->toBe(10);
});

it('seller dapat melakukan penyesuaian stok', function () {
    [$seller, $store] = stockSeller();
    $product = stockProduct($store);

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
    [$seller, $store] = stockSeller();
    $product = stockProduct($store);

    actingAs($seller)
        ->get('/inventory')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Inventory/Index')
            ->has('products.data', 1)
            ->where('products.total', 1)
            ->where('products.data.0.id', $product->id)
            ->where('lowStockCount', 1));
});

it('riwayat stok menampilkan daftar movement', function () {
    [$seller, $store] = stockSeller();
    $product = stockProduct($store);
    app(StockService::class)->stockIn($product, 5, 'Restock', $seller);

    actingAs($seller)
        ->get("/inventory/{$product->id}/history")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Inventory/History')
            ->has('movements', 1)
            ->where('movements.0.type', 'in'));
});
