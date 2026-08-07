<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(protected TenantContext $context) {}

    /**
     * Resolve the current tenant from the request host's subdomain.
     *
     * - `{slug}.platform.com` → load Tenant by slug and set the context.
     * - No subdomain / `app.` / `www.` → platform context, no tenant.
     * - Unknown subdomain → 404 (store not found).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $this->subdomain($request->getHost(), strval(config('platform.base_domain', '')));

        if ($subdomain === null) {
            $this->context->clear();

            return $next($request);
        }

        $tenant = Tenant::query()
            ->where('slug', $subdomain)
            ->where('status', 'active')
            ->first();

        if (! $tenant) {
            abort(404, 'Store not found.');
        }

        $this->context->set($tenant);

        return $next($request);
    }

    /**
     * Extract the meaningful subdomain label, ignoring the base domain,
     * `www`, and the platform host itself. Returns null for platform requests.
     */
    protected function subdomain(string $host, string $baseDomain): ?string
    {
        $host = strtolower((string) $host);
        $baseDomain = strtolower(trim($baseDomain, " \t\n\r\0\x0B."));

        if ($baseDomain !== '' && str_ends_with($host, '.'.$baseDomain)) {
            $sub = substr($host, 0, -(strlen($baseDomain) + 1));
            $labels = explode('.', $sub);

            return $this->validSubdomain(end($labels));
        }

        $labels = explode('.', $host);

        return count($labels) >= 3 ? $this->validSubdomain($labels[0]) : null;
    }

    protected function validSubdomain(?string $sub): ?string
    {
        if ($sub === null || $sub === '' || in_array($sub, ['www', 'app'], true)) {
            return null;
        }

        return $sub;
    }
}
