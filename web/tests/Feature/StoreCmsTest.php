<?php

use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;

use function Pest\Laravel\actingAs;

function cmsSeller(): User
{
    $user = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $user->id]);
    Store::factory()->create(['tenant_id' => $tenant->id, 'slug' => 'toko-punya-seller']);

    return $user;
}

function cmsStore(): Store
{
    return Store::where('slug', 'toko-punya-seller')->firstOrFail();
}

it('seller dapat membuka halaman Tampilan & Konten', function () {
    actingAs(cmsSeller())
        ->get('/store-cms')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('StoreSettings/Cms')
            ->has('theme')
            ->has('themes')
            ->has('showcase.hero')
            ->has('products'));
});

it('seller dapat memperbarui tema dan showcase', function () {
    $seller = cmsSeller();
    $store = cmsStore();
    $product = Product::factory()->create(['store_id' => $store->id]);

    actingAs($seller)
        ->put('/store-cms', [
            'theme' => 'teal',
            'theme_colors' => [
                'primary' => '#3F9AAE',
                'secondary' => '#79C9C5',
                'accent' => '#FFE2AF',
                'strong' => '#F96E5B',
            ],
            'showcase' => [
                'hero' => [
                    'title' => 'Judul Hero Baru',
                    'subtitle' => 'Sub judul baru',
                    'cta_label' => 'Beli Sekarang',
                ],
                'about' => ['title' => 'Tentang Kami', 'text' => 'Kami jualan halal.'],
                'featured_product_ids' => [$product->id],
                'testimonials' => [
                    ['name' => 'Budi', 'role' => 'Pelanggan', 'text' => 'Bagus!', 'rating' => 5],
                ],
            ],
        ])
        ->assertRedirect();

    $store->refresh();
    expect($store->theme)->toBe('teal');
    expect($store->theme_colors['primary'])->toBe('#3F9AAE');
    expect($store->theme_colors['strong'])->toBe('#F96E5B');
    expect($store->showcase['hero']['title'])->toBe('Judul Hero Baru');
    expect($store->showcase['featured_product_ids'])->toBe([$product->id]);
    expect($store->showcase['testimonials'][0]['name'])->toBe('Budi');
});

it('storefront merender tema dan showcase yang sudah disimpan', function () {
    cmsSeller();
    $store = cmsStore();
    $product = Product::factory()->create(['store_id' => $store->id, 'is_active' => true]);

    $store->update([
        'theme' => 'navy',
        'theme_colors' => [
            'primary' => '#384B70',
            'secondary' => '#507687',
            'accent' => '#FCFAEE',
            'strong' => '#B8001F',
        ],
        'showcase' => [
            'hero' => ['title' => 'Judul Hero', 'subtitle' => 'Sub', 'cta_label' => 'Lihat', 'image' => null],
            'featured_product_ids' => [$product->id],
            'testimonials' => [['name' => 'Ani', 'role' => 'Pembeli', 'text' => 'Top', 'rating' => 4]],
            'about' => ['title' => 'Tentang', 'text' => 'Cerita'],
            'contact' => ['show' => true],
        ],
    ]);

    actingAs(User::factory()->create(['role' => 'buyer']))
        ->get('/store/'.$store->slug)
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('StorePage')
            ->where('theme.key', 'navy')
            ->where('theme.colors.primary', '#384B70')
            ->where('showcase.hero.title', 'Judul Hero')
            ->where('featured.0.id', $product->id)
            ->where('showcase.testimonials.0.name', 'Ani'));
});
