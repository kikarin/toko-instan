<?php

use App\Models\Tenant;
use App\Services\TenantContext;

if (! function_exists('tenant_context')) {
    function tenant_context(): TenantContext
    {
        return app(TenantContext::class);
    }
}

if (! function_exists('tenant')) {
    /**
     * The current tenant resolved during the request, or null.
     */
    function tenant(): ?Tenant
    {
        return tenant_context()->get();
    }
}

if (! function_exists('tenantId')) {
    function tenantId(): ?int
    {
        return tenant_context()->id();
    }
}
