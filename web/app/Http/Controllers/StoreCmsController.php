<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Services\StoreCmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreCmsController extends Controller
{
    public function __construct(protected StoreCmsService $cmsService) {}

    public function edit(Request $request): Response|RedirectResponse
    {
        $store = $this->storeForUser($request->user()->id);

        if (! $store) {
            return Inertia::render('StoreSettings/Edit');
        }
        $products = $store->products()
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => 'Rp '.number_format($p->price, 0, ',', '.'),
                'img' => $p->img,
            ]);

        return Inertia::render('StoreSettings/Cms', [
            'store' => $store,
            'theme' => $this->cmsService->resolve($store),
            'showcase' => $this->cmsService->normalize($store->showcase),
            'themes' => $this->cmsService->themes(),
            'products' => $products,
        ]);
    }

    public function update(Request $request): RedirectResponse|Response
    {
        $store = $this->storeOwnerOrFail($request->user()->id);

        $validated = $request->validate([
            'theme' => 'required|string|in:teal,sky,navy,sand,forest,custom',
            'theme_colors.primary' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'theme_colors.secondary' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'theme_colors.accent' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'theme_colors.strong' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'showcase.hero.title' => 'nullable|string|max:255',
            'showcase.hero.subtitle' => 'nullable|string|max:500',
            'showcase.hero.cta_label' => 'nullable|string|max:100',
            'showcase.hero.image' => 'nullable|url',
            'showcase.about.title' => 'nullable|string|max:255',
            'showcase.about.text' => 'nullable|string|max:2000',
            'showcase.contact.show' => 'nullable|boolean',
            'showcase.featured_product_ids' => 'nullable|array',
            'showcase.featured_product_ids.*' => 'integer',
            'showcase.testimonials' => 'nullable|array',
            'showcase.testimonials.*.name' => 'nullable|string|max:255',
            'showcase.testimonials.*.role' => 'nullable|string|max:255',
            'showcase.testimonials.*.text' => 'nullable|string|max:2000',
            'showcase.testimonials.*.rating' => 'nullable|integer|between:1,5',
        ]);

        $showcase = $this->cmsService->normalize($store->showcase);
        $rawShowcase = $validated['showcase'] ?? [];

        foreach ($rawShowcase as $section => $values) {
            $showcase[$section] = array_replace($showcase[$section] ?? [], $values);
        }

        $store->update([
            'theme' => $validated['theme'],
            'theme_colors' => $validated['theme_colors'],
            'showcase' => $showcase,
        ]);

        return redirect()->back()->with('success', 'Tampilan & konten toko berhasil diperbarui!');
    }

    private function storeForUser(int $userId): ?Store
    {
        return Store::whereHas('tenant', fn ($q) => $q->where('user_id', $userId))->first();
    }

    private function storeOwnerOrFail(int $userId): Store
    {
        return Store::whereHas('tenant', fn ($q) => $q->where('user_id', $userId))->firstOrFail();
    }
}
