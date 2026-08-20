<?php

use App\Contracts\AiProvider;
use App\Gateways\FakeAiProvider;
use App\Gateways\OpenAiProvider;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AiCopyService;
use Illuminate\Support\Facades\Http;

test('ai provider is config driven', function () {
    config(['ai.driver' => 'fake']);
    expect(app(AiProvider::class))->toBeInstanceOf(FakeAiProvider::class);

    config(['ai.driver' => 'openai', 'ai.openai.key' => 'sk-test']);
    expect(app(AiProvider::class))->toBeInstanceOf(OpenAiProvider::class);
});

test('fake ai copy fills description seo and caption', function () {
    $copy = app(AiCopyService::class)->generateProductCopy([
        'name' => 'Air Max Pulse',
        'category' => 'Sepatu',
        'brand' => 'Nike',
        'price' => 1990000,
        'tasks' => ['description', 'seo', 'caption'],
    ]);

    expect($copy['description'])->toContain('Air Max Pulse')
        ->and($copy['meta_title'])->not->toBeEmpty()
        ->and($copy['meta_description'])->not->toBeEmpty()
        ->and($copy['tags'])->not->toBeEmpty()
        ->and($copy['marketing_caption'])->toContain('Air Max Pulse');
});

test('seller can generate product copy via endpoint', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    Store::factory()->create(['tenant_id' => $tenant->id]);

    $payload = $this->actingAs($seller)->postJson('/products/ai-generate', [
        'name' => 'Hoodie Fleece',
        'category' => 'Apparel',
        'brand' => 'Nike',
        'tasks' => ['description', 'seo', 'caption'],
    ])->assertOk()
        ->assertJsonStructure(['description', 'meta_title', 'meta_description', 'tags', 'marketing_caption'])
        ->json();

    expect($payload['description'])->toContain('Hoodie Fleece');
});

test('openai provider posts chat completions', function () {
    config(['ai.openai.key' => 'sk-test', 'ai.openai.model' => 'gpt-4o-mini']);
    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [[
                'message' => ['content' => json_encode([
                    'description' => 'Deskripsi OpenAI',
                    'meta_title' => 'Judul',
                    'meta_description' => 'Desc',
                    'tags' => ['a'],
                    'marketing_caption' => 'Cap',
                ])],
            ]],
        ], 200),
    ]);

    $text = app(OpenAiProvider::class)->complete('Nama produk: Tes');
    expect($text)->toContain('Deskripsi OpenAI');
    Http::assertSent(fn ($request) => str_contains($request->url(), 'chat/completions'));
});

test('product create persists ai seo fields', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    Store::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($seller)->post('/products', [
        'name' => 'Jaket Bomber',
        'category' => 'Fashion',
        'price' => 350000,
        'stock' => 8,
        'meta_title' => 'Jaket Bomber Original',
        'meta_description' => 'Jaket bomber original siap kirim.',
        'seo_tags' => 'jaket, bomber',
        'marketing_caption' => 'Jaket bomber ready stock!',
    ])->assertRedirect();

    $this->assertDatabaseHas('products', [
        'name' => 'Jaket Bomber',
        'meta_title' => 'Jaket Bomber Original',
        'seo_tags' => 'jaket, bomber',
    ]);
});
