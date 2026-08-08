<?php

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\TenantContext;

use function Pest\Laravel\actingAs;

function auditUser(): User
{
    return User::factory()->create(['role' => 'seller']);
}

function auditTenant(User $user): Tenant
{
    return Tenant::factory()->create(['user_id' => $user->id]);
}

it('mencatat aktivitas dengan tenant, actor, aksi dan properti', function () {
    $user = auditUser();
    $tenant = auditTenant($user);
    $product = Product::factory()->create(['store_id' => Store::factory()->create(['tenant_id' => $tenant->id])->id]);

    actingAs($user);
    app(TenantContext::class)->set($tenant);

    app(ActivityLogService::class)->record('updated', Product::class, $product->id, ['price' => 150000]);

    $log = ActivityLog::firstOrFail();

    expect($log->tenant_id)->toBe($tenant->id);
    expect($log->user_id)->toBe($user->id);
    expect($log->action)->toBe('updated');
    expect($log->subject_type)->toBe(Product::class);
    expect($log->subject_id)->toBe((string) $product->id);
    expect($log->properties)->toBe(['price' => 150000]);
    expect($log->ip)->not->toBeNull();
});

it('aktivitas tanpa tenant dan user masih bisa tercatat', function () {
    app(ActivityLogService::class)->record('system_task', null, null, []);

    $log = ActivityLog::firstOrFail();

    expect($log->tenant_id)->toBeNull();
    expect($log->user_id)->toBeNull();
    expect($log->action)->toBe('system_task');
});

it('created_at tercatat dalam zona waktu asia jakarta', function () {
    app(ActivityLogService::class)->record('created', Product::class, 1);

    $log = ActivityLog::firstOrFail();

    expect(config('app.timezone'))->toBe('Asia/Jakarta');
    expect($log->created_at->timezoneName)->toBe('Asia/Jakarta');
});

it('ledger immutable - hanya created_at yang dicatat', function () {
    $log = ActivityLog::factory()->create();

    expect(ActivityLog::find($log->id)->action)->toBe('updated');
    expect(ActivityLog::find($log->id)->updated_at)->toBeNull();
});
