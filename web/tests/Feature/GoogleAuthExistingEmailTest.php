<?php

use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\FirebaseAuthService;
use Illuminate\Support\Facades\Hash;

test('google seller register promotes existing buyer with same email', function () {
    $tenant = Tenant::factory()->create();
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);

    $buyer = User::factory()->create([
        'name' => 'Buyer Lama',
        'email' => 'buyer-upgrade@example.com',
        'role' => 'buyer',
        'store_id' => $store->id,
        'password' => Hash::make('secret12'),
        'firebase_uid' => null,
        'auth_provider' => 'email',
    ]);

    $this->mock(FirebaseAuthService::class, function ($mock) {
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->with('token-upgrade')
            ->andReturn([
                'uid' => 'firebase-upgrade-uid',
                'email' => 'buyer-upgrade@example.com',
                'name' => 'Seller Baru',
            ]);
    });

    $this->post('/auth/google', [
        'id_token' => 'token-upgrade',
        'intent' => 'register',
        'store_name' => 'Toko Upgrade',
        'store_slug' => 'toko-upgrade',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($buyer->fresh());

    $buyer->refresh();
    expect($buyer->role)->toBe('seller');
    expect($buyer->store_id)->toBeNull();
    expect($buyer->firebase_uid)->toBe('firebase-upgrade-uid');
    expect($buyer->auth_provider)->toBe('google');
    $this->assertDatabaseHas('stores', [
        'slug' => 'toko-upgrade',
        'name' => 'Toko Upgrade',
    ]);
    expect(User::where('email', 'buyer-upgrade@example.com')->count())->toBe(1);
});

test('google seller register links existing seller without creating duplicate', function () {
    $seller = User::factory()->create([
        'email' => 'seller-link@example.com',
        'role' => 'seller',
        'store_id' => null,
        'firebase_uid' => null,
        'auth_provider' => 'email',
    ]);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    Store::factory()->create([
        'tenant_id' => $tenant->id,
        'slug' => 'toko-lama',
        'name' => 'Toko Lama',
    ]);

    $this->mock(FirebaseAuthService::class, function ($mock) {
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->andReturn([
                'uid' => 'firebase-link-uid',
                'email' => 'seller-link@example.com',
                'name' => 'Seller Link',
            ]);
    });

    $this->post('/auth/google', [
        'id_token' => 'token-link',
        'intent' => 'register',
        'store_name' => 'Toko Tidak Dibuat',
        'store_slug' => 'toko-tidak-dibuat',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($seller->fresh());
    expect($seller->fresh()->firebase_uid)->toBe('firebase-link-uid');
    $this->assertDatabaseMissing('stores', ['slug' => 'toko-tidak-dibuat']);
    expect(User::where('email', 'seller-link@example.com')->count())->toBe(1);
});
