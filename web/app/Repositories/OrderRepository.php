<?php

namespace App\Repositories;

use App\DTO\CreateOrderDTO;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    public function getCompletedOrdersSum(?int $storeId = null): float
    {
        return (float) Order::query()
            ->where('status', 'completed')
            ->when($storeId !== null, fn ($q) => $q->where('store_id', $storeId))
            ->sum('total_amount');
    }

    public function countTotalOrders(?int $storeId = null): int
    {
        return (int) Order::query()
            ->when($storeId !== null, fn ($q) => $q->where('store_id', $storeId))
            ->count();
    }

    public function getAverageOrderValue(?int $storeId = null): float
    {
        $count = $this->countTotalOrders($storeId);

        if ($count === 0) {
            return 0.0;
        }

        return (float) Order::query()
            ->when($storeId !== null, fn ($q) => $q->where('store_id', $storeId))
            ->avg('total_amount');
    }

    public function countByStatus(string $status, ?int $storeId = null): int
    {
        return (int) Order::query()
            ->where('status', $status)
            ->when($storeId !== null, fn ($q) => $q->where('store_id', $storeId))
            ->count();
    }

    public function countByCustomerEmailAndStatus(string $email, string $status): int
    {
        return Order::where('customer_email', $email)->where('status', $status)->count();
    }

    public function createOrder(CreateOrderDTO $dto, string $orderNumber, float $totalAmount): Order
    {
        return DB::transaction(function () use ($dto, $orderNumber, $totalAmount) {
            $storeId = $this->resolveStoreId($dto);
            $tenantId = Store::query()->whereKey($storeId)->value('tenant_id');

            $order = Order::create([
                'store_id' => $storeId,
                'order_number' => $orderNumber,
                'customer_name' => $dto->customerName,
                'customer_email' => $dto->customerEmail,
                'customer_phone' => $dto->customerPhone,
                'shipping_address' => $dto->shippingAddress,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'notes' => $dto->notes,
            ]);

            foreach ($dto->items as $item) {
                $this->createOrderItem($order, $tenantId, $item);
            }

            return $order->load('items');
        });
    }

    /**
     * @param  array{id?: int, variant_id?: int, name?: string, price?: float|int, qty: int}  $item
     */
    protected function createOrderItem(Order $order, ?int $tenantId, array $item): OrderItem
    {
        $product = isset($item['id']) ? Product::query()->find($item['id']) : null;

        if (! $product) {
            throw new \RuntimeException('Produk tidak ditemukan.');
        }

        $variant = isset($item['variant_id'])
            ? ProductVariant::query()->where('product_id', $product->id)->find($item['variant_id'])
            : null;

        if (isset($item['variant_id']) && $variant === null) {
            throw new \RuntimeException('Varian produk tidak valid.');
        }

        $price = (float) ($variant?->price ?? $product->price ?? 0);
        $qty = max(1, (int) ($item['qty'] ?? 1));
        $name = (string) ($variant?->name ?? $product->name ?? 'Produk');
        $sku = $variant?->sku ?? $product->sku;

        if ($variant !== null && filled($variant->name) && ! str_contains($name, $variant->name)) {
            $name = trim($product->name.' — '.$variant->name);
        }

        return OrderItem::create([
            'tenant_id' => $tenantId,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'name' => $name,
            'sku' => $sku,
            'price' => $price,
            'qty' => $qty,
            'total' => $price * $qty,
        ]);
    }

    protected function resolveStoreId(CreateOrderDTO $dto): int
    {
        $storeIds = [];

        foreach ($dto->items as $item) {
            if (! isset($item['id'])) {
                continue;
            }

            $storeId = Product::query()->whereKey($item['id'])->value('store_id');
            if ($storeId === null) {
                throw new \RuntimeException('Produk tidak ditemukan.');
            }

            $storeIds[] = (int) $storeId;
        }

        if (count(array_unique($storeIds)) > 1) {
            throw new \RuntimeException('Item pesanan berasal dari toko yang berbeda.');
        }

        return $storeIds[0] ?? throw new \RuntimeException('Pesanan tidak memiliki produk.');
    }

    /**
     * @return Collection<int, Order>
     */
    public function getRecent(int $limit = 5)
    {
        return Order::with(['store', 'items'])->orderByDesc('created_at')->take($limit)->get();
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return Order::with(['store', 'items'])->where('order_number', $orderNumber)->first();
    }

    /**
     * @return Collection<int, Order>
     */
    public function getByBuyerEmail(string $email)
    {
        return Order::with(['store', 'items'])
            ->where('customer_email', $email)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @return Collection<int, Order>
     */
    public function getSellerOrders(int $storeId)
    {
        return Order::with(['store', 'items'])
            ->where('store_id', $storeId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @return Collection<int, Order>
     */
    public function getCustomersByStore(int $storeId)
    {
        return Order::where('store_id', $storeId)
            ->select('customer_name', 'customer_email', 'customer_phone')
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('SUM(total_amount) as total_spent')
            ->selectRaw('MIN(created_at) as first_order_at')
            ->selectRaw('MAX(created_at) as last_order_at')
            ->groupBy('customer_email', 'customer_name', 'customer_phone')
            ->orderByDesc('last_order_at')
            ->get();
    }

    public function findByOrderNumberWithTenant(string $orderNumber): ?Order
    {
        return Order::with('store.tenant')->where('order_number', $orderNumber)->first();
    }

    public function findOrFail(int $id): Order
    {
        return Order::findOrFail($id);
    }
}
