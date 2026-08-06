<?php

namespace App\Services;

use App\DTO\ProductData;
use App\Models\Product;
use App\Models\Store;
use App\Repositories\ProductRepository;
use App\Repositories\StoreRepository;
use Illuminate\Database\Eloquent\Collection;
use LogicException;

class ProductService
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected StoreRepository $storeRepository
    ) {}

    /**
     * @return Collection<int, Product>
     */
    public function listForSeller(): Collection
    {
        $store = $this->storeRepository->getPrimaryStore();

        if (! $store) {
            return new Collection;
        }

        return $this->productRepository->getForStore($store->id);
    }

    public function create(ProductData $data): Product
    {
        return $this->productRepository->createForStore($this->requireStore()->id, $data);
    }

    public function findForSeller(int $id): ?Product
    {
        $store = $this->storeRepository->getPrimaryStore();

        if (! $store) {
            return null;
        }

        return $this->productRepository->findForStore($id, $store->id);
    }

    public function update(Product $product, ProductData $data): void
    {
        $this->requireStore();
        $this->productRepository->updateProduct($product, $data);
    }

    public function delete(Product $product): void
    {
        $this->requireStore();
        $this->productRepository->deleteProduct($product);
    }

    public function updateStock(Product $product, int $stock): void
    {
        $this->requireStore();
        $this->productRepository->updateStock($product, $stock);
    }

    public function toggleActive(Product $product): bool
    {
        $this->requireStore();

        return $this->productRepository->toggleActive($product);
    }

    /**
     * @return array<int, string>
     */
    public function categories(): array
    {
        return ['Fashion', 'Elektronik', 'Sepatu', 'Aksesoris', 'Kuliner'];
    }

    /**
     * @return array<string, mixed>
     */
    public function format(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category,
            'price' => $product->price,
            'formatted_price' => 'Rp '.number_format($product->price, 0, ',', '.'),
            'stock' => $product->stock,
            'is_active' => (bool) $product->is_active,
            'sold' => $product->sold,
            'rating' => (float) $product->rating,
            'tag' => $product->tag,
            'img' => $product->img,
        ];
    }

    private function requireStore(): Store
    {
        $store = $this->storeRepository->getPrimaryStore();

        if (! $store) {
            throw new LogicException('Toko belum tersedia untuk akun ini.');
        }

        return $store;
    }
}
