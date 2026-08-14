<?php

use App\Mail\VerifyEmailMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

test('register queues verification email and redirects to verify notice', function () {
    Mail::fake();

    $this->post('/register', [
        'name' => 'Seller Verify',
        'email' => 'seller-verify@example.com',
        'password' => 'secret12',
        'store_name' => 'Toko Verify',
        'store_slug' => 'toko-verify',
    ])->assertRedirect(route('verification.notice'));

    $this->assertAuthenticated();
    expect(auth()->user()->hasVerifiedEmail())->toBeFalse();
    Mail::assertQueued(VerifyEmailMail::class);
});

test('unverified seller cannot open dashboard', function () {
    $seller = User::factory()->unverified()->state(['role' => 'seller', 'store_id' => null])->create();

    $this->actingAs($seller)
        ->get('/dashboard')
        ->assertRedirect(route('verification.notice'));
});

test('signed verification link marks email verified', function () {
    $seller = User::factory()->unverified()->state(['role' => 'seller', 'store_id' => null])->create();

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $seller->id, 'hash' => sha1($seller->email)]
    );

    $this->actingAs($seller)
        ->get($url)
        ->assertRedirect('/dashboard');

    expect($seller->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('verify email page renders for authenticated unverified user', function () {
    $seller = User::factory()->unverified()->state(['role' => 'seller', 'store_id' => null])->create();

    $this->actingAs($seller)
        ->get('/email/verify')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Auth/VerifyEmail'));
});
