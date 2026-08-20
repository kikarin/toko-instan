<?php

use App\Mail\ResetPasswordMail;
use App\Models\Store;
use App\Models\User;
use App\Repositories\PasswordResetRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

function buyerForStore(Store $store): User
{
    $buyer = User::factory()->state(['role' => 'buyer'])->create();

    $buyer->update(['store_id' => $store->id]);

    return $buyer;
}

function storeWithSlug(string $slug): Store
{
    return Store::factory()->create(['slug' => $slug]);
}

test('a buyer can view the store forgot password page', function () {
    $store = storeWithSlug('toko-a');

    $this->get("/{$store->slug}/forgot-password")->assertOk();
});

test('a store buyer receives a reset link scoped to the correct store', function () {
    Mail::fake();

    $store = storeWithSlug('toko-a');
    $buyer = buyerForStore($store);

    $this->post("/{$store->slug}/forgot-password", ['email' => $buyer->email])
        ->assertRedirect();

    Mail::assertQueued(ResetPasswordMail::class, function (ResetPasswordMail $mail) use ($store) {
        return str_contains($mail->resetUrl, "/{$store->slug}/reset-password/");
    });

    expect(
        DB::table('password_reset_tokens')
            ->where('email', $buyer->email)
            ->where('store_id', $store->id)
            ->exists()
    )->toBeTrue();
});

test('a reset link is not created for an email not registered in the store', function () {
    Mail::fake();

    $store = storeWithSlug('toko-a');

    $this->post("/{$store->slug}/forgot-password", ['email' => 'ghost@example.com'])
        ->assertRedirect();

    Mail::assertNothingQueued();
    expect(DB::table('password_reset_tokens')->where('email', 'ghost@example.com')->exists())
        ->toBeFalse();
});

test('the same email in a different store does not receive a reset link', function () {
    Mail::fake();

    $storeA = storeWithSlug('toko-a');
    $storeB = storeWithSlug('toko-b');
    $buyer = buyerForStore($storeA);

    $this->post("/{$storeB->slug}/forgot-password", ['email' => $buyer->email])
        ->assertRedirect();

    Mail::assertNothingQueued();
    expect(
        DB::table('password_reset_tokens')
            ->where('email', $buyer->email)
            ->where('store_id', $storeB->id)
            ->exists()
    )->toBeFalse();
});

test('a buyer can reset their password with a valid store token', function () {
    $store = storeWithSlug('toko-a');
    $buyer = buyerForStore($store);

    app(PasswordResetRepository::class)->store($buyer->email, $store->id, 'plain-token');

    $this->get("/{$store->slug}/reset-password/plain-token")->assertOk();

    $this->post("/{$store->slug}/reset-password", [
        'email' => $buyer->email,
        'password' => 'rahasia-baru',
        'token' => 'plain-token',
    ])->assertRedirect("/{$store->slug}/login");

    expect(Hash::check('rahasia-baru', $buyer->fresh()->password))->toBeTrue();
    expect(
        DB::table('password_reset_tokens')
            ->where('email', $buyer->email)
            ->where('store_id', $store->id)
            ->exists()
    )->toBeFalse();
});

test('a reset token for one store cannot be used in another store', function () {
    $storeA = storeWithSlug('toko-a');
    $storeB = storeWithSlug('toko-b');
    $buyer = buyerForStore($storeA);

    app(PasswordResetRepository::class)->store($buyer->email, $storeA->id, 'plain-token');

    $this->post("/{$storeB->slug}/reset-password", [
        'email' => $buyer->email,
        'password' => 'rahasia-baru',
        'token' => 'plain-token',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('rahasia-baru', $buyer->fresh()->password))->toBeFalse();
});

test('a platform seller can reset their password', function () {
    Mail::fake();

    $seller = User::factory()->state(['role' => 'seller'])->create();

    $this->post('/forgot-password', ['email' => $seller->email])->assertRedirect();

    Mail::assertQueued(ResetPasswordMail::class, function (ResetPasswordMail $mail) {
        return str_contains($mail->resetUrl, '/reset-password/')
            && ! str_contains($mail->resetUrl, '/{store_slug}/');
    });

    app(PasswordResetRepository::class)->store($seller->email, null, 'plain-token');

    $this->post('/reset-password', [
        'email' => $seller->email,
        'password' => 'rahasia-baru',
        'token' => 'plain-token',
    ])->assertRedirect(route('login'));

    expect(Hash::check('rahasia-baru', $seller->fresh()->password))->toBeTrue();
});

test('an expired reset token is rejected', function () {
    $store = storeWithSlug('toko-a');
    $buyer = buyerForStore($store);

    DB::table('password_reset_tokens')->insert([
        'email' => $buyer->email,
        'store_id' => $store->id,
        'token' => hash('sha256', 'expired-token'),
        'created_at' => now()->subMinutes(61),
    ]);

    $this->post("/{$store->slug}/reset-password", [
        'email' => $buyer->email,
        'password' => 'rahasia-baru',
        'token' => 'expired-token',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('rahasia-baru', $buyer->fresh()->password))->toBeFalse();
});

test('an invalid reset token is rejected', function () {
    $store = storeWithSlug('toko-a');
    $buyer = buyerForStore($store);

    $this->post("/{$store->slug}/reset-password", [
        'email' => $buyer->email,
        'password' => 'rahasia-baru',
        'token' => 'not-the-token',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('rahasia-baru', $buyer->fresh()->password))->toBeFalse();
});
