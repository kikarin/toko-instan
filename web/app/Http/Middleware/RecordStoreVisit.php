<?php

namespace App\Http\Middleware;

use App\Models\Store;
use App\Models\StoreVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordStoreVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $slug = $request->route('store_slug') ?? $request->route('slug');
        if (! is_string($slug) || $slug === '' || $request->user()?->role === 'seller') {
            return $response;
        }

        try {
            $storeId = Store::query()->where('slug', $slug)->value('id');
            if (! $storeId) {
                return $response;
            }

            StoreVisit::query()->firstOrCreate([
                'store_id' => $storeId,
                'visited_on' => now()->toDateString(),
                'session_key' => substr(sha1($request->session()->getId() ?: $request->ip() ?: 'anon'), 0, 32),
            ], [
                'path' => substr($request->path(), 0, 180),
            ]);
        } catch (Throwable) {
            // Unique race or missing session — skip.
        }

        return $response;
    }
}
