<?php

use App\Actions\CreateTenantAndStore;
use App\Models\User;

test('create tenant and store action provisions tenant plus store for seller', function () {
    $seller = User::factory()->state(['role' => 'seller'])->create();

    $result = app(CreateTenantAndStore::class)($seller->id, 'Toko Baru SE', 'toko-baru-se');

    expect($result['tenant']->user_id)->toBe($seller->id)
        ->and($result['store']->slug)->toBe('toko-baru-se')
        ->and($result['store']->tenant_id)->toBe($result['tenant']->id);
});
