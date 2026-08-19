<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || $user->role !== 'seller') {
            abort(403, 'API hanya untuk seller.');
        }

        if (! $user->primaryTenant()->exists() && ! $user->tenants()->exists()) {
            abort(403, 'Tenant tidak ditemukan.');
        }

        return $next($request);
    }
}
