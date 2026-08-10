<?php

namespace App\Http\Controllers;

use App\DTO\Store\StoreSettingsDTO;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreSettingsController extends Controller
{
    public function __construct(protected StoreService $storeService) {}

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

        if ($store) {
            $dto = StoreSettingsDTO::fromRequest($request, $store->id);
            $this->storeService->updateSettings($store, $dto);
        }

        return redirect()->back()->with('success', 'Pengaturan Toko berhasil diperbarui!');
    }

    private function resolve(int $userId): ?Store
    {
        return $this->storeService->getActiveStore($userId);
    }
}
