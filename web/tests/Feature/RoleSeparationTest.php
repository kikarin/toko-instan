<?php

use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;

function roleUser(string $role): User
{
    return User::factory()->state(['role' => $role])->create();
}

test('a guest cannot access any role-protected area', function () {
    collect(['/orders', '/dashboard', '/products', '/admin', '/inventory', '/wallet'])
        ->each(fn ($uri) => $this->get($uri)->assertRedirect('/login'));
});

test('a logged-in buyer cannot access the seller dashboard or admin areas', function () {
    $buyer = roleUser('buyer');
    $store = Store::factory()->create();
    $buyer->update(['store_id' => $store->id]);

    $this->actingAs($buyer)->get('/dashboard')->assertRedirect("/{$store->slug}");
    $this->actingAs($buyer)->get('/admin')->assertRedirect("/{$store->slug}");
});

test('a seller cannot access the buyer-only area or admin areas', function () {
    $seller = roleUser('seller');
    $store = Store::factory()->create();

    $this->actingAs($seller)->get('/admin')->assertRedirect('/dashboard');
    $this->actingAs($seller)->get('/admin/users')->assertRedirect('/dashboard');
});

test('an admin can access the admin dashboard and user management', function () {
    $admin = roleUser('admin');

    $this->actingAs($admin)->get('/admin')->assertOk();
    $this->actingAs($admin)->get('/admin/users')->assertOk();
});

test('an admin can access every role area', function () {
    $admin = roleUser('admin');
    $store = Store::factory()->create();

    $this->actingAs($admin)->get('/admin')->assertOk();
    $this->actingAs($admin)->get('/products')->assertOk();
    $this->actingAs($admin)->get("/{$store->slug}")->assertOk();
});

test('a seller is redirected to dashboard when visiting another store', function () {
    $seller = roleUser('seller');
    $store = Store::factory()->create();

    $this->actingAs($seller)->get("/{$store->slug}")->assertRedirect('/dashboard');
    $this->actingAs($seller)->get("/{$store->slug}/checkout")->assertRedirect('/dashboard');
});

test('a seller can preview their own storefront', function () {
    $seller = roleUser('seller');
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($seller)->get("/{$store->slug}")->assertOk();
});

test('an authenticated user is sent to their home path from the landing page', function () {
    $buyer = roleUser('buyer');
    $store = Store::factory()->create();
    $buyer->update(['store_id' => $store->id]);
    $this->actingAs($buyer)->get('/')->assertRedirect("/{$store->slug}");

    $seller = roleUser('seller');
    $this->actingAs($seller)->get('/')->assertRedirect('/dashboard');

    $admin = roleUser('admin');
    $this->actingAs($admin)->get('/')->assertRedirect('/admin');
});

test('an admin can soft delete a user', function () {
    $admin = roleUser('admin');
    $target = roleUser('buyer');

    $this->actingAs($admin)->delete("/admin/users/{$target->id}")->assertRedirect(route('admin.users'));

    $this->assertSoftDeleted('users', ['id' => $target->id]);
});
