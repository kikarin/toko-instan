<?php

use App\Models\User;
use App\Support\PublicMediaUrl;
use Illuminate\Support\Facades\Storage;

test('public media url rewrites blocked r2.dev hosts to app proxy', function () {
    $url = 'https://pub-62f031e342a640c78e57096a41697019.r2.dev/products/2026/08/foto_medium.webp';

    expect(PublicMediaUrl::rewrite($url))
        ->toEndWith('/media/products/2026/08/foto_medium.webp')
        ->and(PublicMediaUrl::rewrite('https://images.unsplash.com/photo.jpg'))
        ->toBe('https://images.unsplash.com/photo.jpg')
        ->and(PublicMediaUrl::rewrite(null))->toBeNull();
});

test('media proxy streams public product files from r2', function () {
    Storage::fake('r2');
    Storage::disk('r2')->put('products/2026/08/demo.webp', 'webp-bytes');

    $this->get('/media/products/2026/08/demo.webp')
        ->assertOk();
});

test('media proxy rejects digital product files', function () {
    Storage::fake('r2');
    Storage::disk('r2')->put('digital/secret.pdf', 'pdf');

    $this->get('/media/digital/secret.pdf')->assertNotFound();
});

test('guest can view media proxy without auth', function () {
    Storage::fake('r2');
    Storage::disk('r2')->put('products/x.webp', 'x');

    $this->get('/media/products/x.webp')->assertOk();
    $this->actingAs(User::factory()->create())->get('/media/products/x.webp')->assertOk();
});
