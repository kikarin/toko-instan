<?php

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Mail\SubscriptionExpiredMail;
use App\Mail\SubscriptionRenewalMail;
use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    config([
        'services.midtrans.server_key' => 'SB-Mid-server-test',
        'services.midtrans.client_key' => 'SB-Mid-client-test',
        'services.midtrans.is_production' => false,
    ]);
});

test('plans free and premium are seeded', function () {
    expect(Plan::query()->where('code', 'free')->exists())->toBeTrue()
        ->and(Plan::query()->where('code', 'premium')->value('price'))->toBe(99000)
        ->and(Plan::query()->where('code', 'premium')->value('withdraw_fee'))->toBe(0)
        ->and(Plan::query()->where('code', 'premium')->value('settlement_mode'))->toBe('direct');
});

test('activating premium sets tenant plan and subscription window', function () {
    $tenant = Tenant::factory()->create(['plan' => 'free']);

    $subscription = app(SubscriptionService::class)->activatePremium($tenant);

    expect($tenant->fresh()->plan)->toBe('premium')
        ->and($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->ends_at?->isFuture())->toBeTrue()
        ->and(app(SubscriptionService::class)->isPremium($tenant->fresh()))->toBeTrue()
        ->and(app(SubscriptionService::class)->withdrawFeeFor($tenant->fresh()))->toBe(0);
});

test('midtrans subscription webhook activates premium', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id, 'plan' => 'free']);
    Store::factory()->create(['tenant_id' => $tenant->id]);

    $orderId = 'SUB-'.$tenant->id.'-TEST01';

    SubscriptionPayment::query()->create([
        'tenant_id' => $tenant->id,
        'provider' => PaymentProvider::Midtrans,
        'amount' => 99000,
        'status' => PaymentStatus::Pending,
        'external_id' => $orderId,
        'idempotency_key' => 'sub-init:'.$orderId,
    ]);

    $payload = [
        'order_id' => $orderId,
        'transaction_id' => 'sub-trx-1',
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'status_code' => '200',
        'gross_amount' => '99000.00',
    ];
    $payload['signature_key'] = hash(
        'sha512',
        $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'SB-Mid-server-test'
    );

    $this->postJson('/webhooks/midtrans', $payload)->assertOk();

    expect($tenant->fresh()->plan)->toBe('premium')
        ->and(Subscription::where('tenant_id', $tenant->id)->where('status', 'active')->exists())->toBeTrue();
});

test('expired subscriptions downgrade to free and send mail', function () {
    Mail::fake();

    $seller = User::factory()->create(['role' => 'seller', 'email' => 'seller-sub@example.com']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id, 'plan' => 'premium']);
    $premium = Plan::query()->where('code', 'premium')->firstOrFail();

    Subscription::query()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $premium->id,
        'status' => SubscriptionStatus::Active,
        'starts_at' => now()->subMonths(2),
        'ends_at' => now()->subDay(),
    ]);

    $this->artisan('subscriptions:process')->assertSuccessful();

    expect($tenant->fresh()->plan)->toBe('free');
    expect(Subscription::where('tenant_id', $tenant->id)->first()?->status)->toBe(SubscriptionStatus::Expired);

    Mail::assertQueued(SubscriptionExpiredMail::class);
});

test('renewal reminder is sent once within 7 days', function () {
    Mail::fake();

    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id, 'plan' => 'premium']);
    $premium = Plan::query()->where('code', 'premium')->firstOrFail();

    Subscription::query()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $premium->id,
        'status' => SubscriptionStatus::Active,
        'starts_at' => now()->subDays(23),
        'ends_at' => now()->addDays(3),
    ]);

    $this->artisan('subscriptions:process')->assertSuccessful();
    $this->artisan('subscriptions:process')->assertSuccessful();

    Mail::assertQueued(SubscriptionRenewalMail::class, 1);
});

test('seller subscription page is reachable', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id, 'plan' => 'free']);
    Store::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($seller)
        ->get('/subscription')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Subscription/Index'));
});

test('upgrade creates snap payment when midtrans responds', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id, 'plan' => 'free']);
    Store::factory()->create(['tenant_id' => $tenant->id]);

    Http::fake([
        'app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'snap-sub-token',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/pay/x',
        ], 201),
    ]);

    $this->actingAs($seller)
        ->post('/subscription/upgrade')
        ->assertRedirect();

    expect(SubscriptionPayment::where('tenant_id', $tenant->id)->where('snap_token', 'snap-sub-token')->exists())->toBeTrue();
});
