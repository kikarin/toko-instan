<?php

use App\Mail\OtpLoginMail;
use App\Models\Store;
use App\Models\User;
use App\Repositories\LoginOtpRepository;
use Illuminate\Support\Facades\Mail;

test('otp login page renders', function () {
    $this->get('/otp-login')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Auth/OtpLogin'));
});

test('sending otp queues mail for registered seller', function () {
    Mail::fake();

    $seller = User::factory()->state([
        'role' => 'seller',
        'email' => 'otp-seller@example.com',
        'store_id' => null,
    ])->create();

    $this->post('/otp-login', ['email' => $seller->email])
        ->assertRedirect();

    Mail::assertQueued(OtpLoginMail::class);
});

test('seller can login with valid otp code', function () {
    Mail::fake();

    $seller = User::factory()->state([
        'role' => 'seller',
        'email' => 'otp-login@example.com',
        'store_id' => null,
    ])->create();

    app(LoginOtpRepository::class)->store($seller->email, null, '123456');

    $this->post('/otp-login/verify', [
        'email' => $seller->email,
        'code' => '123456',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($seller->fresh());
});

test('invalid otp is rejected', function () {
    $seller = User::factory()->state([
        'role' => 'seller',
        'email' => 'otp-bad@example.com',
        'store_id' => null,
    ])->create();

    app(LoginOtpRepository::class)->store($seller->email, null, '123456');

    $this->from('/otp-login')->post('/otp-login/verify', [
        'email' => $seller->email,
        'code' => '000000',
    ])->assertSessionHasErrors('code');

    $this->assertGuest();
});

test('storefront buyer can login via otp', function () {
    $store = Store::factory()->create(['slug' => 'toko-otp']);
    $buyer = User::factory()->state([
        'role' => 'buyer',
        'email' => 'buyer-otp@example.com',
        'store_id' => $store->id,
    ])->create();

    app(LoginOtpRepository::class)->store($buyer->email, $store->id, '654321');

    $this->post("/{$store->slug}/otp-login/verify", [
        'email' => $buyer->email,
        'code' => '654321',
    ])->assertRedirect('/'.$store->slug);

    $this->assertAuthenticatedAs($buyer->fresh());
});
