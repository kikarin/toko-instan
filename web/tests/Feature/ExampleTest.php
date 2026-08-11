<?php

use App\Models\Store;

test('landing page has a successful response', function () {
    $this->get('/')->assertOk();
});

test('guest browsing is redirected away from the buyer checkout area', function () {
    $store = Store::factory()->create();

    $this->get("/{$store->slug}/checkout")->assertRedirect('/login');
});
