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

it('warna tema yang disimpan muncul kembali di halaman Tampilan & Konten', function () {
    $seller = cmsSeller();
    $store = cmsStore();

    $palette = [
        'primary' => '#123456',
        'secondary' => '#ABCDEF',
        'accent' => '#FEDCBA',
        'strong' => '#654321',
    ];

    actingAs($seller)
        ->put('/store-cms', [
            'theme' => 'custom',
            'theme_colors' => $palette,
            'showcase' => [
                'hero' => ['title' => 'Judul Hero Baru'],
                'about' => ['title' => 'Tentang Kami', 'text' => 'Kami jualan halal.'],
                'contact' => ['show' => true],
                'featured_product_ids' => [],
                'testimonials' => [],
            ],
        ])
        ->assertRedirect();

    $store->refresh();
    expect($store->theme)->toBe('custom');

    actingAs($seller)
        ->get('/store-cms')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('StoreSettings/Cms')
            ->where('theme.key', 'custom')
            ->where('theme.colors.primary', '#123456')
            ->where('theme.colors.secondary', '#ABCDEF')
            ->where('theme.colors.accent', '#FEDCBA')
            ->where('theme.colors.strong', '#654321'));
});
