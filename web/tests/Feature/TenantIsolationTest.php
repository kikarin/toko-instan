<?php

use App\Http\Middleware\IdentifyTenant;
use App\Models\Store;
use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

function runTenantMiddleware(string $url): void
{
    $middleware = new IdentifyTenant(app(TenantContext::class));
    $middleware->handle(Request::create($url), fn () => response('ok'));
}

test('a subdomain host resolves the matching tenant context', function () {
    $tenant = Tenant::factory()->state(['slug' => 'demo-store'])->create();

    runTenantMiddleware('http://demo-store.toko-instan.test/login');

    expect(tenantId())->toBe($tenant->id);
    expect(tenant()?->is($tenant))->toBeTrue();
});

test('platform host (no subdomain) leaves the tenant context empty', function () {
    runTenantMiddleware('http://toko-instan.test/login');

    expect(app(TenantContext::class)->get())->toBeNull();
    expect(tenantId())->toBeNull();
});

test('www and app host labels are not treated as tenants', function () {
    $tenantA = Tenant::factory()->create();

    runTenantMiddleware('http://www.toko-instan.test/login');
    expect(tenantId())->toBeNull();

    runTenantMiddleware('http://app.toko-instan.test/login');
    expect(tenantId())->toBeNull();
});

test('an unknown subdomain returns 404', function () {
    expect(fn () => runTenantMiddleware('http://does-not-exist.sekarang.test/login'))
        ->toThrow(NotFoundHttpException::class);
});

test('store queries are isolated by the resolved tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    $storeA = Store::factory()->create(['tenant_id' => $tenantA->id]);
    Store::factory()->create(['tenant_id' => $tenantB->id]);

    runTenantMiddleware('http://'.$tenantA->slug.'.toko-instan.test/login');

    $stores = Store::all();

    expect($stores->pluck('id'))->toContain($storeA->id)
        ->and($stores->count())->toBe(1);
});

test('the tenant context is re-resolved per request', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    runTenantMiddleware('http://'.$tenantA->slug.'.toko-instan.test/login');
    expect(tenantId())->toBe($tenantA->id);

    runTenantMiddleware('http://'.$tenantB->slug.'.toko-instan.test/login');
    expect(tenantId())->toBe($tenantB->id);

    runTenantMiddleware('http://toko-instan.test/login');
    expect(tenantId())->toBeNull();
});
