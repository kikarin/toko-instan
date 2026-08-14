<?php

namespace App\Http\Middleware;

use App\Repositories\StoreRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforce storefront isolation:
 *
 * - Guests may roam and buy from any store (public marketplace stays open).
 * - Buyers are locked to the store they registered at (`users.store_id`).
 * - Sellers may only preview their own store.
 * - Admins may preview every store.
 */
class EnsureStoreAccess
{
    public function __construct(protected StoreRepository $storeRepository) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('store_slug');

        if (! is_string($slug) || $slug === '') {
            abort(404);
        }

        $store = $this->storeRepository->findBySlug($slug);

        if (! $store) {
            abort(404);
        }

        $user = $request->user();

        // Guests may browse every storefront freely.
        if (! $user) {
            return $next($request);
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($user->role === 'buyer') {
            if ($user->store_id === $store->id) {
                return $next($request);
            }

            return redirect($this->buyerHomePath($user));
        }

        if ($user->role === 'seller') {
            $tenant = $store->tenant;

            if ($tenant !== null && $tenant->user_id === $user->id) {
                return $next($request);
            }

            return redirect($user->homePath());
        }

        return $next($request);
    }

    /**
     * Home store of the buyer, falling back to the platform login when the
     * buyer has no store attached yet (avoids a redirect loop on `/`).
     */
    protected function buyerHomePath(mixed $user): string
    {
        $path = $user->homePath();

        return $path === '/' ? '/login' : $path;
    }
}
