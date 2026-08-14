<?php

use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\FirebaseAuthService;
use Illuminate\Support\Facades\Hash;

test('inactive storefront shows closed page', function () {
    $tenant = Tenant::factory()->create();
    Store::factory()->create([
        'tenant_id' => $tenant->id,
        'slug' => 'toko-tutup',
        'is_active' => false,
        'name' => 'Toko Tutup',
    ]);

    $this->get('/toko-tutup')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('StoreClosed')
            ->where('store.slug', 'toko-tutup'));
});

test('platform register page is seller onboarding not seeded storefront', function () {
    $tenant = Tenant::factory()->create();
    Store::factory()->create([
        'tenant_id' => $tenant->id,
        'slug' => 'nike-indonesia',
        'name' => 'Nike Official Store',
        'is_active' => true,
    ]);

    $this->get('/register')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Auth/Register')
            ->where('intent', 'platform')
            ->where('store', null));
});

test('platform register creates seller with custom slug', function () {
    $this->post('/register', [
        'name' => 'Seller Baru',
        'email' => 'seller-baru@example.com',
        'password' => 'secret12',
        'store_name' => 'Batik Indah',
        'store_slug' => 'batik-indah',
    ])->assertRedirect('/email/verify');

    $this->assertAuthenticated();
    expect(auth()->user()->role)->toBe('seller');
    $this->assertDatabaseHas('stores', ['slug' => 'batik-indah', 'name' => 'Batik Indah']);
});

test('google auth verifies id token and logs in existing user', function () {
    $user = User::factory()->create([
        'email' => 'google-user@example.com',
        'role' => 'seller',
        'password' => Hash::make('not-used'),
        'firebase_uid' => null,
        'store_id' => null,
    ]);

    $this->mock(FirebaseAuthService::class, function ($mock) {
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->with('valid-token')
            ->andReturn([
                'uid' => 'firebase-uid-123',
                'email' => 'google-user@example.com',
                'name' => 'Google User',
                'picture' => null,
            ]);
    });

    $this->post('/auth/google', [
        'id_token' => 'valid-token',
        'intent' => 'login',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user->fresh());
    expect($user->fresh()->firebase_uid)->toBe('firebase-uid-123');
});

test('seller dashboard kpis are scoped without dummy fallbacks', function () {
    $seller = User::factory()->state(['role' => 'seller'])->create();
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    Store::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Toko KPI']);

    $this->actingAs($seller)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('kpis', 6)
            ->where('kpis.0.value', 'Rp 0')
            ->where('kpis.1.value', '0')
            ->missing('topSellers.0'));
});
