<?php

namespace App\Http\Controllers;

use App\Services\CustomDomainService;
use App\Services\StoreService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreDomainController extends Controller
{
    public function __construct(
        protected StoreService $storeService,
        protected CustomDomainService $domains,
        protected SubscriptionService $subscriptions,
    ) {}

    public function edit(Request $request): Response
    {
        $store = $this->storeService->getActiveStore($request->user()->id);
        $premium = $store?->tenant ? $this->subscriptions->isPremium($store->tenant) : false;

        return Inertia::render('StoreSettings/Domain', [
            'store' => $store ? [
                'name' => $store->name,
                'slug' => $store->slug,
                'custom_domain' => $store->custom_domain,
                'custom_domain_status' => $store->custom_domain_status,
            ] : null,
            'is_premium' => $premium,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $store = $this->storeService->getActiveStore($request->user()->id);
        abort_unless($store, 404);
        $validated = $request->validate(['custom_domain' => ['required', 'string', 'max:180']]);
        $this->domains->attach($store, $validated['custom_domain']);

        return back()->with('success', 'Custom domain disimpan.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $store = $this->storeService->getActiveStore($request->user()->id);
        abort_unless($store, 404);
        $this->domains->detach($store);

        return back()->with('success', 'Custom domain dilepas.');
    }
}
