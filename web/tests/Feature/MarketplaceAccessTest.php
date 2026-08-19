<?php

use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;

test('a seller can view a storefront directly', function () {
    $seller = User::factory()->state(['role' => 'seller'])->create();
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($seller)->get("/{$store->slug}")->assertOk();
});

test('a buyer cannot access the admin panel', function () {
    $buyer = User::factory()->state(['role' => 'buyer'])->create();
    $store = Store::factory()->create();
    $buyer->update(['store_id' => $store->id]);
    $admin = User::factory()->state(['role' => 'admin'])->create();

    $this->actingAs($buyer)->get('/admin')->assertRedirect("/{$store->slug}");
    $this->actingAs($buyer)->get('/admin/users')->assertRedirect("/{$store->slug}");

    $this->actingAs($admin)->get('/admin')->assertOk();
    $this->actingAs($admin)->get('/admin/users')->assertOk();
});

test('an admin can impersonate a buyer without the buyer retaining admin access', function () {
    $admin = User::factory()->state(['role' => 'admin'])->create();
    $buyer = User::factory()->state(['role' => 'buyer'])->create();

    $this->actingAs($admin)->post("/admin/users/{$buyer->id}/impersonate");

    $this->assertAuthenticatedAs($buyer);
    $this->get('/admin')->assertRedirect('/');
});
