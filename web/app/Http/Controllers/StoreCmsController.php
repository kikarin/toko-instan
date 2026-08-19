<?php

namespace App\Http\Controllers;

use App\DTO\Store\StoreCmsDTO;
use App\Models\Store;
use App\Repositories\ProductRepository;
use App\Services\StoreCmsService;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreCmsController extends Controller
{
    public function __construct(
        protected StoreCmsService $cmsService,
        protected StoreService $storeService,
        protected ProductRepository $productRepository
    ) {}

    public function edit(Request $request): Response|RedirectResponse
    {
        $store = $this->storeForUser($request->user()->id);

        if (! $store) {
            return Inertia::render('StoreSettings/Edit');
        }
        $products = $this->productRepository->getActiveProductsForStore($store->id)
            ->map(fn ($p) => [
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

        $dto = StoreCmsDTO::fromRequest($request);
        $this->cmsService->updateCms($store, $dto);

        return redirect()->back()->with('success', 'Tampilan & konten toko berhasil diperbarui!');
    }

    private function storeForUser(int $userId): ?Store
    {
        return $this->storeService->getStoreForUser($userId);
    }

    private function storeOwnerOrFail(int $userId): Store
    {
        $store = $this->storeService->getStoreForUser($userId);

        if (! $store) {
            abort(404, 'Store tidak ditemukan');
        }

        return $store;
    }
}
