<?php

namespace App\Http\Middleware;

use App\Models\Store;
use App\Services\StoreCmsService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'theme' => $this->activeTheme(),
        ];
    }

    /**
     * Resolve the active storefront theme from the primary store.
     *
     * @return array{colors: array{primary: string, secondary: string, accent: string, strong: string}}|null
     */
    private function activeTheme(): ?array
    {
        $store = Store::first();

        if (! $store) {
            return null;
        }

        $theme = app(StoreCmsService::class)->resolve($store);

        return [
            'key' => $theme['key'],
            'colors' => $theme['colors'],
        ];
    }
}
