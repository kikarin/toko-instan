<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicStorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->store = Store::factory()->create([
            'tenant_id' => $this->tenant->id,
            'slug' => 'toko-satu',
            'is_active' => true,
        ]);
        
        $this->product = Product::factory()->create([
            'store_id' => $this->store->id,
            'slug' => 'sepatu-lari',
            'is_active' => true,
        ]);
    }

    public function test_public_can_view_store_catalog_without_auth(): void
    {
        $response = $this->get('/toko-satu');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('StorePage')
            ->has('store')
            ->where('store.slug', 'toko-satu')
            ->has('products', 1)
        );
    }

    public function test_public_can_view_product_detail_without_auth(): void
    {
        $response = $this->get('/toko-satu/p/sepatu-lari');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('ProductDetail')
            ->has('store')
            ->has('product')
            ->where('product.slug', 'sepatu-lari')
        );
    }

    public function test_returns_404_for_unknown_store(): void
    {
        $response = $this->get('/toko-palsu');
        $response->assertStatus(404);
    }

    public function test_returns_404_for_unknown_product(): void
    {
        $response = $this->get('/toko-satu/p/produk-palsu');
        $response->assertStatus(404);
    }
}
