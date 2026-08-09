<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Repositories\StoreRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class StoreSettingsController extends Controller
{
    public function __construct(protected StoreRepository $storeRepository) {}

    public function edit(Request $request): Response|RedirectResponse
    {
        $store = $this->resolve($request->user()->id);

        return Inertia::render('StoreSettings/Edit', [
            'store' => $store,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $store = $this->resolve($request->user()->id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:stores,slug,'.($store?->id ?: 0),
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|string|max:500',
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'avatar_hue' => 'nullable|integer|between:0,360',
            'banner_url' => 'nullable|string|max:500',
            'banner_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'banner_files' => 'nullable|array|max:5',
            'banner_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'existing_banners' => 'nullable|array',
            'existing_banners.*' => 'string|max:500',
            'highlights' => 'nullable|array|max:3',
            'highlights.*' => 'string|max:50',
            'hero_config' => 'nullable|array',
            'hero_config.about_text' => 'nullable|string|max:255',
            'hero_config.widget_title' => 'nullable|string|max:50',
            'hero_config.widget_subtitle' => 'nullable|string|max:100',
            'hero_config.widget_description' => 'nullable|string|max:150',
            'hero_config.fake_buyer_count' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:50', 
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'instagram' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'headline' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'npwp' => 'nullable|string|max:30',
            'nik' => 'nullable|string|max:30',
            'is_pkp' => 'nullable|boolean',
            'tax_name' => 'nullable|string|max:255',
            'tax_address' => 'nullable|string',
        ]);

        if ($request->hasFile('banner_file')) {
            $bannerPath = $request->file('banner_file')->store('banners', 'public');
            $validated['banner_url'] = '/storage/'.$bannerPath;
        }

        $bannerUrls = $request->input('existing_banners', []);
        
        if ($request->hasFile('banner_files')) {
            foreach ($request->file('banner_files') as $file) {
                $path = $file->store('banners', 'public');
                $bannerUrls[] = '/storage/'.$path;
            }
        }
        $validated['banner_urls'] = $bannerUrls;

        if ($request->hasFile('logo_file')) {
            $logoPath = $request->file('logo_file')->store('logos', 'public');
            $validated['logo'] = '/storage/'.$logoPath;
        }

        if ($store) {
            $updatable = array_filter($validated, function ($val, $key) {
                return Schema::hasColumn('stores', $key);
            }, ARRAY_FILTER_USE_BOTH);

            $store->update($updatable);
        }

        return redirect()->back()->with('success', 'Pengaturan Toko berhasil diperbarui!');
    }

    private function resolve(int $userId): ?Store
    {
        return $this->storeRepository->getActiveStore($userId);
    }
}
