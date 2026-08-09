<?php

use App\Models\User;

test('a seller can view the marketplace directly', function () {
    $seller = User::factory()->state(['role' => 'seller'])->create();

    $this->actingAs($seller)->get('/marketplace')->assertOk();
});

test('a buyer cannot access the admin panel', function () {
    $buyer = User::factory()->state(['role' => 'buyer'])->create();
    $admin = User::factory()->state(['role' => 'admin'])->create();

    $this->actingAs($buyer)->get('/admin')->assertRedirect('/marketplace');
    $this->actingAs($buyer)->get('/admin/users')->assertRedirect('/marketplace');

    $this->actingAs($admin)->get('/admin')->assertOk();
    $this->actingAs($admin)->get('/admin/users')->assertOk();
});

test('an admin can impersonate a buyer without the buyer retaining admin access', function () {
    $admin = User::factory()->state(['role' => 'admin'])->create();
    $buyer = User::factory()->state(['role' => 'buyer'])->create();

    $this->actingAs($admin)->post("/admin/users/{$buyer->id}/impersonate");

    $this->assertAuthenticatedAs($buyer);
    $this->get('/admin')->assertRedirect('/marketplace');
});
