<?php

use App\Models\User;

function roleUser(string $role): User
{
    return User::factory()->state(['role' => $role])->create();
}

test('a guest cannot access any role-protected area', function () {
    collect(['/marketplace', '/checkout', '/orders', '/dashboard', '/products', '/admin'])
        ->each(fn ($uri) => $this->get($uri)->assertRedirect('/login'));
});

test('a logged-in buyer cannot access the seller dashboard or admin areas', function () {
    $buyer = roleUser('buyer');

    $this->actingAs($buyer)->get('/dashboard')->assertRedirect('/marketplace');
    $this->actingAs($buyer)->get('/admin')->assertRedirect('/marketplace');
});

test('a seller cannot access the buyer-only area or admin areas', function () {
    $seller = roleUser('seller');

    $this->actingAs($seller)->get('/checkout')->assertRedirect('/dashboard');
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

    $this->actingAs($admin)->get('/marketplace')->assertOk();
    $this->actingAs($admin)->get('/products')->assertOk();
});

test('a seller cannot shop on the marketplace checkout', function () {
    $seller = roleUser('seller');

    $this->actingAs($seller)->get('/checkout')->assertRedirect('/dashboard');
});

test('an authenticated user is sent to their home path from the landing page', function () {
    $buyer = roleUser('buyer');
    $this->actingAs($buyer)->get('/')->assertRedirect('/marketplace');

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
