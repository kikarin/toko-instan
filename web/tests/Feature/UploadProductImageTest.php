<?php

use App\Actions\UploadProductImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('upload product image action stores original and generates three variants', function () {
    Storage::fake('r2');

    $file = UploadedFile::fake()->image('produk.jpg', 800, 600);

    $result = app(UploadProductImage::class)($file);

    expect($result['url'])->toBeString()->not->toBeEmpty()
        ->and($result['urls'])->toHaveKeys(['thumbnail', 'medium', 'large'])
        ->and($result['path'])->toBeString()->not->toBeEmpty();

    Storage::disk('r2')->assertExists($result['path']);
});

test('authenticated seller can upload an image via uploads endpoint', function () {
    Storage::fake('r2');

    $seller = User::factory()->state(['role' => 'seller'])->create();
    $file = UploadedFile::fake()->image('banner.png');

    $this->actingAs($seller)
        ->postJson(route('uploads.store'), ['file' => $file])
        ->assertOk()
        ->assertJsonStructure(['url', 'path', 'urls' => ['thumbnail', 'medium', 'large']]);
});

test('guest cannot upload images', function () {
    Storage::fake('r2');

    $this->postJson(route('uploads.store'), [
        'file' => UploadedFile::fake()->image('x.jpg'),
    ])->assertUnauthorized();
});
