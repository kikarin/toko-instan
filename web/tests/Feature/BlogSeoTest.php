<?php

use App\Models\BlogPost;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;

test('seller can create publish and delete a blog post', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($seller)->post('/blog/categories', ['name' => 'Tips'])->assertRedirect();

    $this->actingAs($seller)->post('/blog', [
        'title' => 'Cara merawat sepatu',
        'excerpt' => 'Ringkasan',
        'body' => 'Isi artikel lengkap.',
        'tags' => 'sepatu, perawatan',
        'is_published' => true,
        'meta_title' => 'Merawat sepatu',
        'meta_description' => 'Tips merawat sepatu di rumah',
    ])->assertRedirect();

    $post = BlogPost::query()->where('store_id', $store->id)->first();
    expect($post)->not->toBeNull()
        ->and($post->is_published)->toBeTrue()
        ->and($post->tags)->toHaveCount(2);

    $this->get("/{$store->slug}/blog")->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Blog/StoreIndex')
            ->has('posts', 1)
            ->has('seo.title'));

    $this->get("/{$store->slug}/blog/{$post->slug}")->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Blog/Show')
            ->where('post.title', 'Cara merawat sepatu'));

    $this->actingAs($seller)->delete("/blog/{$post->id}")->assertRedirect();
    expect(BlogPost::query()->find($post->id))->toBeNull();
});

test('draft blog posts are hidden from the storefront', function () {
    $store = Store::factory()->create();
    BlogPost::factory()->create([
        'store_id' => $store->id,
        'tenant_id' => $store->tenant_id,
        'is_published' => false,
        'published_at' => null,
        'slug' => 'draft-only',
    ]);

    $this->get("/{$store->slug}/blog")->assertOk()
        ->assertInertia(fn ($page) => $page->has('posts', 0));
    $this->get("/{$store->slug}/blog/draft-only")->assertNotFound();
});

test('store sitemap and robots include products and blog', function () {
    $store = Store::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'is_active' => true,
        'slug' => 'air-max',
    ]);
    $post = BlogPost::factory()->create([
        'store_id' => $store->id,
        'tenant_id' => $store->tenant_id,
        'slug' => 'tips-lari',
        'is_published' => true,
        'published_at' => now()->subHour(),
    ]);

    $this->get("/{$store->slug}/sitemap.xml")
        ->assertOk()
        ->assertHeader('content-type', 'application/xml; charset=UTF-8')
        ->assertSee($store->slug.'/p/'.$product->slug, false)
        ->assertSee($store->slug.'/blog/'.$post->slug, false);

    $this->get("/{$store->slug}/robots.txt")
        ->assertOk()
        ->assertSee('Sitemap:', false)
        ->assertSee($store->slug.'/sitemap.xml', false);
});

test('product page exposes json-ld product schema', function () {
    $store = Store::factory()->create();
    $product = Product::factory()->create([
        'store_id' => $store->id,
        'is_active' => true,
        'name' => 'Sepatu Lari',
        'slug' => 'sepatu-lari',
        'price' => 199000,
        'sku' => 'SKU-1',
        'stock' => 4,
    ]);

    $this->get("/{$store->slug}/p/{$product->slug}")->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('ProductDetail')
            ->where('seo.json_ld.@type', 'Product')
            ->where('seo.json_ld.offers.priceCurrency', 'IDR')
            ->has('seo.og_title')
            ->has('seo.description'));
});
