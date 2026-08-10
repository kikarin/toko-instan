<?php

namespace App\Services;

use App\DTO\Store\StoreSettingsDTO;
use App\Models\Store;
use App\Repositories\StoreRepository;
use Illuminate\Support\Facades\Schema;

class StoreService
{
    public function __construct(protected StoreRepository $storeRepository) {}

    public function getActiveStore(?int $userId): ?Store
    {
        return $this->storeRepository->getActiveStore($userId);
    }

    public function getStoreForUser(int $userId): ?Store
    {
        return $this->storeRepository->getStoreForUser($userId);
    }

    public function updateSettings(Store $store, StoreSettingsDTO $dto): void
    {
        $validated = $dto->validatedData;

        if ($dto->bannerFile) {
            $bannerPath = $dto->bannerFile->store('banners', 'public');
            $validated['banner_url'] = '/storage/'.$bannerPath;
        }

        $bannerUrls = $validated['existing_banners'] ?? [];

        foreach ($dto->bannerFiles as $file) {
            $path = $file->store('banners', 'public');
            $bannerUrls[] = '/storage/'.$path;
        }
        $validated['banner_urls'] = $bannerUrls;

        if ($dto->logoFile) {
            $logoPath = $dto->logoFile->store('logos', 'public');
            $validated['logo'] = '/storage/'.$logoPath;
        }

        $updatable = array_filter($validated, function ($val, $key) {
            return Schema::hasColumn('stores', $key);
        }, ARRAY_FILTER_USE_BOTH);

        $this->storeRepository->update($store, $updatable);
    }
}
