<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RewriteCustomDomainRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower((string) $request->getHost());
        if ($host === '' || in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return $next($request);
        }

        $path = '/'.ltrim($request->path(), '/');
        if ($path === '//') {
            $path = '/';
        }

        $reserved = explode('|', (string) config('platform.reserved_paths', 'admin|login|register|dashboard|api|horizon|up|media|webhooks'));
        $first = explode('/', ltrim($path, '/'))[0] ?? '';
        if (in_array($first, $reserved, true)) {
            return $next($request);
        }

        $store = Store::query()
            ->whereRaw('LOWER(custom_domain) = ?', [$host])
            ->where('custom_domain_status', 'active')
            ->first();

        if (! $store) {
            return $next($request);
        }

        $slug = $store->slug;
        if ($path === '/' || $path === '/'.$slug || str_starts_with($path, '/'.$slug.'/')) {
            if ($path === '/') {
                $this->rewrite($request, '/'.$slug);
            }

            return $next($request);
        }

        $this->rewrite($request, '/'.$slug.$path);

        return $next($request);
    }

    protected function rewrite(Request $request, string $uri): void
    {
        $qs = $request->getQueryString();
        $request->server->set('REQUEST_URI', $qs ? $uri.'?'.$qs : $uri);
    }
}
