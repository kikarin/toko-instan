<?php

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\TenantContext;
use LogicException;

use function Pest\Laravel\actingAs;

function auditUser(): User
{
    return User::factory()->create(['role' => 'seller']);
}

function auditTenant(User $user): Tenant
{
    return Tenant::factory()->create(['user_id' => $user->id]);
}

function auditStore(Tenant $tenant): Store
{
    return Store::factory()->create(['tenant_id' => $tenant->id]);
}

it('mencatat aktivitas dengan tenant, actor, aksi dan properti', function () {
    $user = auditUser();
    $tenant = auditTenant($user);
    $store = auditStore($tenant);

    actingAs($user);
    app(TenantContext::class)->set($tenant);

    $product = Product::factory()->create(['store_id' => $store->id]);

    app(ActivityLogService::class)->record('updated', Product::class, $product->id, ['price' => 150000]);

    $log = ActivityLog::where('action', 'updated')->firstOrFail();

    expect($log->tenant_id)->toBe($tenant->id);
    expect($log->user_id)->toBe($user->id);
    expect($log->subject_type)->toBe(Product::class);
    expect($log->subject_id)->toBe((string) $product->id);
    expect($log->properties)->toBe(['price' => 150000]);
    expect($log->ip)->not->toBeNull();
});

it('aktivitas tanpa tenant dan user masih bisa tercatat', function () {
    app(ActivityLogService::class)->record('system_task', null, null, []);

    $log = ActivityLog::where('action', 'system_task')->firstOrFail();

    expect($log->tenant_id)->toBeNull();
    expect($log->user_id)->toBeNull();
});

it('created_at tercatat dalam zona waktu asia jakarta', function () {
    app(ActivityLogService::class)->record('created', Product::class, 1);

    $log = ActivityLog::where('action', 'created')->latest('id')->firstOrFail();

    expect(config('app.timezone'))->toBe('Asia/Jakarta');
    expect($log->created_at->timezoneName)->toBe('Asia/Jakarta');
});

it('ledger immutable - hanya created_at yang dicatat', function () {
    $log = ActivityLog::factory()->create();

    expect(ActivityLog::find($log->id)->action)->toBe('updated');
    expect(ActivityLog::find($log->id)->updated_at)->toBeNull();
});

it('activity log tidak bisa diubah atau dihapus', function () {
    $log = ActivityLog::factory()->create();

    expect(fn () => $log->update(['action' => 'changed']))
        ->toThrow(LogicException::class, 'immutable');

    expect(fn () => $log->delete())
        ->toThrow(LogicException::class, 'immutable');
});

it('resolusi tenant tetap berjalan di dashboard seller tanpa subdomain', function () {
    $user = auditUser();
    $tenant = auditTenant($user);
    auditStore($tenant);

    actingAs($user);

    app(ActivityLogService::class)->record('created', Product::class, 1);

    $log = ActivityLog::where('action', 'created')->latest('id')->firstOrFail();

    expect($log->tenant_id)->toBe($tenant->id);
});

it('observer product mencatat pembuatan, perubahan dan penghapusan', function () {
    $user = auditUser();
    $tenant = auditTenant($user);
    $store = auditStore($tenant);

    actingAs($user);
    app(TenantContext::class)->set($tenant);

    $product = Product::factory()->create(['store_id' => $store->id, 'name' => 'Sepatu Original']);
    $product->update(['price' => 210000]);

    expect(ActivityLog::where('action', 'created')->where('subject_id', (string) $product->id)->exists())->toBeTrue();

    $updated = ActivityLog::where('action', 'updated')->latest('id')->firstOrFail();
    expect($updated->properties['name'])->toBe('Sepatu Original');
    expect($updated->properties['changes']['price'])->toBe(210000);

    $product->delete();
    expect(ActivityLog::where('action', 'deleted')->where('subject_id', (string) $product->id)->exists())->toBeTrue();
});

it('seller hanya melihat log tokonya sendiri di halaman activity log', function () {
    $user = auditUser();
    $tenant = auditTenant($user);
    $store = auditStore($tenant);

    $otherStore = Store::factory()->create();
    $otherLog = ActivityLog::create([
        'tenant_id' => $otherStore->tenant_id,
        'user_id' => null,
        'action' => 'created',
        'subject_type' => Product::class,
        'subject_id' => '999',
    ]);

    $ownedLog = ActivityLog::create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'action' => 'store_updated',
        'subject_type' => Store::class,
        'subject_id' => (string) $store->id,
    ]);

    actingAs($user)
        ->get('/activity-log')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('ActivityLog/Index')
            ->where('logs.0.id', $ownedLog->id)
            ->where('logs.0.user', $user->name)
            ->has('logs', 1));
});

it('buyer tidak bisa mengakses halaman activity log', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);

    actingAs($buyer)
        ->get('/activity-log')
        ->assertRedirect($buyer->homePath());
});
