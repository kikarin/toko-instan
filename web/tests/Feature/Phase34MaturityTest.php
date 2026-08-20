<?php

use App\Gateways\CloudflareDomainGateway;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReferralCommission;
use App\Models\Store;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\OrderService;
use App\Services\ReferralService;
use App\Services\StoreFaqService;
use Illuminate\Support\Facades\Http;

function twoSellers(): array
{
    $referrerUser = User::factory()->create(['role' => 'seller']);
    $referrer = Tenant::factory()->create(['user_id' => $referrerUser->id, 'referral_code' => 'ABCD1234']);
    Store::factory()->create(['tenant_id' => $referrer->id]);

    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create([
        'user_id' => $seller->id,
        'referred_by_tenant_id' => $referrer->id,
    ]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);

    return [$referrerUser, $referrer, $seller, $tenant, $store];
}

test('referral click is tracked and signup attaches referrer', function () {
    $owner = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $owner->id, 'referral_code' => 'JOINME01']);
    Store::factory()->create(['tenant_id' => $tenant->id]);

    $this->get('/register?ref=JOINME01')->assertOk();
    expect(app(ReferralService::class)->summary($tenant)['clicks'])->toBe(1);

    $this->post('/register', [
        'name' => 'Seller Baru',
        'email' => 'baru-ref@example.com',
        'password' => 'secret12',
        'store_name' => 'Toko Baru Ref',
        'store_slug' => 'toko-baru-ref',
        'referral_code' => 'JOINME01',
    ])->assertRedirect();

    $created = Tenant::query()->where('user_id', User::query()->where('email', 'baru-ref@example.com')->value('id'))->first();
    expect($created?->referred_by_tenant_id)->toBe($tenant->id);
});

test('paid order credits referral commission', function () {
    [, $referrer, , $fromTenant, $store] = twoSellers();
    $order = Order::factory()->create([
        'store_id' => $store->id,
        'total_amount' => 100000,
        'status' => 'pending',
    ]);

    app(OrderService::class)->markOrderPaid($order->fresh(['store.tenant']));

    expect(ReferralCommission::query()->where('tenant_id', $referrer->id)->where('order_id', $order->id)->exists())->toBeTrue();
    expect(WalletTransaction::query()->where('tenant_id', $referrer->id)->where('type', 'referral')->exists())->toBeTrue();
    expect(ActivityLog::query()->where('action', 'referral_commission')->exists())->toBeTrue();
});

test('seller can generate product faqs', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);
    Product::factory()->create(['store_id' => $store->id, 'name' => 'Sepatu Lari', 'is_active' => true]);

    $this->actingAs($seller)->post('/faqs/generate')->assertRedirect();
    expect(app(StoreFaqService::class)->listForStore($store))->not->toBeEmpty();
});

test('seller ticket can be created and answered by admin', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    Store::factory()->create(['tenant_id' => $tenant->id]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($seller)->post('/support', [
        'subject' => 'Gagal withdraw',
        'body' => 'Saldo tidak masuk.',
    ])->assertRedirect();

    $id = SupportTicket::query()->first()->id;
    $this->actingAs($admin)->post("/admin/tickets/{$id}/replies", ['body' => 'Sedang kami cek.'])->assertRedirect();
    expect(SupportTicket::query()->find($id)?->status)->toBe('answered');
});

test('knowledge base is available for sellers', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id]);
    Store::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($seller)->get('/help')->assertOk()
        ->assertInertia(fn ($page) => $page->component('Help/Index')->has('articles'));
});

test('custom domain requires premium', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id, 'plan' => 'free']);
    Store::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($seller)->put('/store-domain', ['custom_domain' => 'shop.contoh.id'])
        ->assertSessionHasErrors('custom_domain');
});

test('premium seller can save custom domain without cloudflare keys', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $tenant = Tenant::factory()->create(['user_id' => $seller->id, 'plan' => 'premium']);
    $store = Store::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($seller)->put('/store-domain', ['custom_domain' => 'shop.contoh.id'])->assertRedirect();
    expect($store->fresh()->custom_domain)->toBe('shop.contoh.id')
        ->and($store->fresh()->custom_domain_status)->toBe('pending');
});

test('cloudflare gateway posts custom hostname', function () {
    config(['services.cloudflare.token' => 'cf', 'services.cloudflare.zone_id' => 'zone']);
    Http::fake(['api.cloudflare.com/*' => Http::response(['success' => true], 200)]);

    $result = app(CloudflareDomainGateway::class)->provisionHostname('a.contoh.id');
    expect($result['status'])->toBe('active');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'custom_hostnames'));
});

test('activity log uses public table on sqlite', function () {
    expect((new ActivityLog)->getTable())->toBe('activity_logs');
});
