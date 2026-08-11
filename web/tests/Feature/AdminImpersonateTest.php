<?php

use App\Http\Controllers\AdminController;
use App\Models\Store;
use App\Models\User;

function adminUser(): User
{
    return User::factory()->state(['role' => 'admin'])->create();
}

test('an admin can impersonate a buyer user', function () {
    $admin = adminUser();
    $buyer = User::factory()->state(['role' => 'buyer'])->create();

    $response = $this->actingAs($admin)
        ->post("/admin/users/{$buyer->id}/impersonate");

    $response->assertRedirect('/');
    $this->assertAuthenticatedAs($buyer);
    $this->assertEquals($admin->id, session()->get(AdminController::SESSION_IMPERSONATED_ADMIN)['id']);
});

test('an impersonated buyer cannot access admin areas', function () {
    $admin = adminUser();
    $buyer = User::factory()->state(['role' => 'buyer'])->create();

    $this->actingAs($admin)->post("/admin/users/{$buyer->id}/impersonate");

    $this->actingAs($buyer)->get('/admin')->assertRedirect('/');
});

test('an admin can stop impersonating and return to admin', function () {
    $admin = adminUser();
    $buyer = User::factory()->state(['role' => 'buyer'])->create();

    $this->actingAs($admin)->post("/admin/users/{$buyer->id}/impersonate");
    $this->assertAuthenticatedAs($buyer);

    $this->post('/admin/impersonate/stop')->assertRedirect(route('admin.users'));

    $this->assertAuthenticatedAs($admin);
    $this->assertNull(session()->get(AdminController::SESSION_IMPERSONATED_ADMIN));
});

test('an admin cannot impersonate another admin', function () {
    $admin = adminUser();
    $other = User::factory()->state(['role' => 'admin'])->create();

    $this->actingAs($admin)
        ->post("/admin/users/{$other->id}/impersonate")
        ->assertRedirect(route('admin.users'));

    $this->assertAuthenticatedAs($admin);
});

test('a non-admin user cannot impersonate a buyer', function () {
    $seller = roleUser('seller');
    $buyer = User::factory()->state(['role' => 'buyer'])->create();

    $this->actingAs($seller)
        ->post("/admin/users/{$buyer->id}/impersonate")
        ->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($seller);
});

test('impersonation info is shared to the frontend', function () {
    $admin = adminUser();
    $buyer = User::factory()->state(['role' => 'buyer'])->create();
    $store = Store::factory()->create();
    $buyer->update(['store_id' => $store->id]);

    $this->actingAs($admin)->post("/admin/users/{$buyer->id}/impersonate");

    $this->get("/{$store->slug}")
        ->assertInertia(fn ($page) => $page
            ->component('StorePage')
            ->where('auth.impersonating.id', $admin->id));
});
