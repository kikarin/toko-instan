<?php

use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Hash;

test('platform register creates seller and ignores buyer role intent', function () {
    $this->post('/register', [
        'name' => 'Seller Only',
        'email' => 'seller-only@example.com',
        'password' => 'secret12',
        'store_name' => 'Toko Seller Only',
        'store_slug' => 'toko-seller-only',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticated();
    expect(auth()->user()->role)->toBe('seller');
    $this->assertDatabaseHas('stores', [
        'slug' => 'toko-seller-only',
        'name' => 'Toko Seller Only',
    ]);
});

test('platform register rejects explicit buyer role', function () {
    $this->from('/register')->post('/register', [
        'name' => 'Buyer Attempt',
        'email' => 'buyer-attempt@example.com',
        'password' => 'secret12',
        'role' => 'buyer',
        'store_name' => 'Should Fail',
        'store_slug' => 'should-fail',
    ])->assertSessionHasErrors('role');
});

test('platform register requires store name and slug', function () {
    $this->post('/register', [
        'name' => 'No Store',
        'email' => 'no-store@example.com',
        'password' => 'secret12',
    ])->assertSessionHasErrors(['store_name', 'store_slug']);
});

test('seller dashboard loads scoped kpis without missing repo methods', function () {
    $seller = User::factory()->state(['role' => 'seller'])->create();
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    Store::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Toko Dash']);

    $this->actingAs($seller)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Dashboard')->has('kpis'));
});

test('admin dashboard includes pending withdrawals stats', function () {
    $admin = User::factory()->state(['role' => 'admin'])->create();
    $seller = User::factory()->state(['role' => 'seller'])->create();
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);

    Withdrawal::factory()->create([
        'store_id' => $store->id,
        'tenant_id' => $tenant->id,
        'amount' => 150000,
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->where('stats.pending_withdrawals', 1)
            ->where('stats.tenants', 1));
});

test('admin tenants and orders modules render', function () {
    $admin = User::factory()->state(['role' => 'admin'])->create();
    $seller = User::factory()->state(['role' => 'seller'])->create();
    $tenant = Tenant::factory()->create([
        'user_id' => $seller->id,
        'name' => 'Tenant Admin',
        'slug' => 'tenant-admin',
    ]);
    Store::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Store Admin',
        'slug' => 'store-admin',
    ]);

    $this->actingAs($admin)
        ->get('/admin/tenants')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Tenants')
            ->has('tenants', 1)
            ->where('tenants.0.slug', 'tenant-admin'));

    $this->actingAs($admin)
        ->get('/admin/orders')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Orders')
            ->has('orders'));
});

test('horizon dashboard is reachable for admin', function () {
    expect(config('horizon.path'))->toBe('horizon');
    expect(file_exists(config_path('horizon.php')))->toBeTrue();

    $admin = User::factory()->state(['role' => 'admin'])->create();

    $this->actingAs($admin)
        ->get('/horizon')
        ->assertOk();
});

test('google login path uses single id-token service entrypoint', function () {
    $user = User::factory()->create([
        'email' => 'google-ok@example.com',
        'role' => 'seller',
        'password' => Hash::make('not-used'),
        'firebase_uid' => null,
        'store_id' => null,
    ]);

    $this->mock(\App\Services\FirebaseAuthService::class, function ($mock) {
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->with('token-ok')
            ->andReturn([
                'uid' => 'uid-ok',
                'email' => 'google-ok@example.com',
                'name' => 'Google OK',
                'picture' => null,
            ]);
    });

    $methods = (new ReflectionClass(\App\Services\AuthService::class))
        ->getMethods(ReflectionMethod::IS_PUBLIC);

    $googleMethods = array_values(array_filter(
        $methods,
        fn (ReflectionMethod $m) => $m->getName() === 'loginWithGoogle' && $m->class === \App\Services\AuthService::class
    ));

    expect($googleMethods)->toHaveCount(1);

    $this->post('/auth/google', [
        'id_token' => 'token-ok',
        'intent' => 'login',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user->fresh());
});
