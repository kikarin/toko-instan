<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class StoreSettingsController extends Controller
{
    public function edit(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        // Get seller's primary store or first store
        $store = Store::whereHas('tenant', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->first();

        if (! $store) {
            $store = Store::first();
        }

        return Inertia::render('StoreSettings/Edit', [
            'store' => $store,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $store = Store::whereHas('tenant', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->first();

        if (! $store) {
            $store = Store::first();
        }

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
}
