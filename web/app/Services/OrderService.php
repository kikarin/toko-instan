<?php

namespace App\Services;

use App\DTO\CreateOrderDTO;
use App\DTO\Order\UpdateOrderStatusDTO;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StoreRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected ProductRepository $productRepository,
        protected StoreRepository $storeRepository,
        protected WalletService $walletService
    ) {}

    public function getCustomerOrderCounts(?string $email): array
    {
        if (! $email) {
            return [
                'bayar' => 0,
                'diproses' => 0,
                'dikirim' => 0,
                'sudah_tiba' => 0,
                'ulasan' => 0,
            ];
        }

        return [
            'bayar' => $this->orderRepository->countByCustomerEmailAndStatus($email, 'pending'),
            'diproses' => $this->orderRepository->countByCustomerEmailAndStatus($email, 'paid'),
            'dikirim' => $this->orderRepository->countByCustomerEmailAndStatus($email, 'shipped'),
            'sudah_tiba' => $this->orderRepository->countByCustomerEmailAndStatus($email, 'completed'),
            'ulasan' => 0,
        ];
    }

    public function processCheckout(CreateOrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            $subtotal = 0;

            foreach ($dto->items as $item) {
                $product = $this->productRepository->find((int) $item['id']);

                if (! $product) {
                    throw new \RuntimeException('Produk tidak ditemukan.');
                }

                if (! $product->is_active) {
                    throw new \RuntimeException("Produk '{$product->name}' sedang tidak aktif.");
                }

                $qty = (int) $item['qty'];
                $price = (float) $product->price;

                if (isset($item['variant_id'])) {
                    $variant = $product->variants()->whereKey($item['variant_id'])->first();

                    if (! $variant) {
                        throw new \RuntimeException('Varian produk tidak valid.');
                    }

                    $price = (float) $variant->price;
                }

                if ((int) $product->stock < $qty) {
                    throw new \RuntimeException("Stok '{$product->name}' tidak mencukupi.");
                }

                $subtotal += $price * $qty;

                $this->productRepository->decrementStock($product->id, $qty);
            }

            $shippingFee = $subtotal >= 300000 ? 0 : 15000;
            $totalAmount = $subtotal + $shippingFee;

            $orderNumber = 'ORD-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $order = $this->orderRepository->createOrder($dto, $orderNumber, $totalAmount);

            $this->storeRepository->incrementTotalOrders($order->store_id);

            return $order;
        });
    }

    public function markOrderPaid(Order $order): void
    {
        if ($order->status === 'paid' || $order->status === 'completed') {
            return;
        }

        $tenantId = $order->store?->tenant_id;
        if ($tenantId === null) {
            throw new \RuntimeException('Order tanpa store tidak bisa diproses escrow.');
        }

        $wallet = $this->walletService->ensureForTenant($tenantId);

        $this->walletService->creditOrderEscrow(
            $wallet->id,
            (float) $order->total_amount,
            $order->id,
            'Escrow penjualan (order '.$order->order_number.')'
        );

        $order->update(['status' => 'paid']);
    }

    public function markOrderCompleted(Order $order): void
    {
        if ($order->status === 'completed') {
            return;
        }

        $tenantId = $order->store?->tenant_id;

        if ($tenantId === null) {
            return;
        }

        $wallet = $this->walletService->ensureForTenant($tenantId);

        $this->walletService->releaseEscrowToAvailable(
            $wallet->id,
            (float) $order->total_amount,
            $order->id
        );

        $order->update(['status' => 'completed']);
    }

    /**
     * @return Collection<int, Order>
     */
    public function buyerOrders($user)
    {
        return collect($this->orderRepository->getByBuyerEmail($user->email));
    }

    public function sellerOrders(int $storeId)
    {
        return collect($this->orderRepository->getSellerOrders($storeId));
    }

    public function getOrderWithTenant(string $orderNumber): ?Order
    {
        return $this->orderRepository->findByOrderNumberWithTenant($orderNumber);
    }

    public function getOrder(int $id): Order
    {
        return $this->orderRepository->findOrFail($id);
    }

    public function updateStatus(Order $order, UpdateOrderStatusDTO $dto): void
    {
        match ($dto->status) {
            'paid' => $this->markOrderPaid($order),
            'completed' => $this->markOrderCompleted($order),
            default => $order->update(['status' => $dto->status]),
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getInvoiceData(string $orderNumber): ?array
    {
        $order = $this->orderRepository->findByOrderNumber($orderNumber);
        if (! $order) {
            return null;
        }

        $items = $order->items->map(fn ($item) => [
            'id' => $item->id,
            'product_name' => $item->name,
            'sku' => $item->sku,
            'qty' => $item->qty,
            'price' => (float) $item->price,
            'subtotal' => (float) $item->total,
            'price_formatted' => 'Rp '.number_format((float) $item->price, 0, ',', '.'),
            'subtotal_formatted' => 'Rp '.number_format((float) $item->total, 0, ',', '.'),
        ])->values()->all();

        $total = (float) $order->total_amount;
        $subtotal = (float) array_sum(array_column($items, 'subtotal'));
        $shippingFee = max(0.0, $total - $subtotal);

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'shipping_address' => $order->shipping_address,
            'notes' => $order->notes,
            'subtotal' => $subtotal,
            'subtotal_formatted' => 'Rp '.number_format($subtotal, 0, ',', '.'),
            'shipping_fee' => $shippingFee,
            'shipping_fee_formatted' => 'Rp '.number_format($shippingFee, 0, ',', '.'),
            'total_amount' => 'Rp '.number_format($total, 0, ',', '.'),
            'total_num' => $total,
            'status' => ucfirst($order->status),
            'store_name' => $order->store ? $order->store->name : 'NovaBatik Studio',
            'created_at' => $order->created_at ? $order->created_at->format('j M Y, H:i') : date('j M Y, H:i'),
            'items' => $items,
        ];
    }
}
