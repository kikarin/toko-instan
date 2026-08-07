<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;

/**
 * Applies a conditional TenantScope so tenant-scoped models are isolated
 * whenever a tenant context is resolved (e.g. subdomain storefront requests).
 *
 * The scope is a no-op when no tenant is resolved, keeping platform-wide
 * queries (dashboard, marketplace) unchanged.
 */
trait ScopedToTenant
{
    public static function bootScopedToTenant(): void
    {
        static::addGlobalScope(new TenantScope);
    }
}
