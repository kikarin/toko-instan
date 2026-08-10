<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AdminController;
use App\Repositories\StoreRepository;
use App\Services\StoreCmsService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(protected StoreRepository $storeRepository) {}

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
                'impersonating' => $request->session()->get(AdminController::SESSION_IMPERSONATED_ADMIN),
            ],
            'theme' => $this->activeTheme($request),
            'store' => $this->activeStore($request),
        ];
    }

    /**
     * Resolve the active storefront theme from the primary store.
     *
     * @return array{colors: array{primary: string, secondary: string, accent: string, strong: string}}|null
     */
    private function activeTheme(Request $request): ?array
    {
        $storeSlug = $request->route('store_slug');
        $store = null;

        if ($storeSlug) {
            $store = $this->storeRepository->findBySlug($storeSlug);
        }

        if (! $store) {
            $store = $this->storeRepository->getActiveStore($request->user()?->id);
        }

        if (! $store) {
            return null;
        }

        $theme = app(StoreCmsService::class)->resolve($store);

        return [
            'key' => $theme['key'],
            'colors' => $theme['colors'],
        ];
    }

    /**
     * Resolve the public storefront data from the primary store so the
     * marketplace (header, hero, footer) follows the seller's store settings.
     *
     * @return array<string, mixed>|null
     */
    private function activeStore(Request $request): ?array
    {
        $storeSlug = $request->route('store_slug');
        $store = null;

        if ($storeSlug) {
            $store = $this->storeRepository->findBySlug($storeSlug);
        }

        if (! $store) {
            $store = $this->storeRepository->getActiveStore($request->user()?->id);
        }

        if (! $store) {
            return null;
        }

        return [
            'id' => $store->id,
            'name' => $store->name,
            'slug' => $store->slug,
            'category' => $store->category,
            'description' => $store->description,
            'logo' => $store->logo,
            'avatar_hue' => $store->avatar_hue,
            'banner_url' => $store->banner_url,
            'headline' => $store->headline,
            'badge' => $store->badge ?: 'Official Store',
            'rating' => (float) $store->rating,
            'phone' => $store->phone,
            'email' => $store->email,
            'address' => $store->address,
            'instagram' => $store->instagram,
            'tiktok' => $store->tiktok,
        ];
    }
}
