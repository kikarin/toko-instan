<?php

namespace App\Services;

use App\DTO\CreateOrderDTO;
use App\Models\Order;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StoreRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected ProductRepository $productRepository,
        protected StoreRepository $storeRepository,
        protected WalletService $walletService
    ) {}

    public function processCheckout(CreateOrderDTO $dto): Order
    {
        $subtotal = 0;
        foreach ($dto->items as $item) {
            $price = (float) $item['price'];
            $qty = (int) $item['qty'];
            $subtotal += ($price * $qty);

            if (isset($item['id'])) {
                $this->productRepository->decrementStock($item['id'], $qty);
            }
        }

        $shippingFee = $subtotal >= 300000 ? 0 : 15000;
        $totalAmount = $subtotal + $shippingFee;

        $orderNumber = 'ORD-'.date('Ymd').'-'.strtoupper(Str::random(4));

        $order = $this->orderRepository->createOrder($dto, $orderNumber, $totalAmount);

        $this->storeRepository->incrementTotalOrders($dto->storeId);

        return $order;
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
    public function buyerOrders(User $user)
    {
        return $this->orderRepository->getByBuyerEmail($user->email);
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

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'total_amount' => 'Rp '.number_format($order->total_amount, 0, ',', '.'),
            'total_num' => (float) $order->total_amount,
            'status' => ucfirst($order->status),
            'store_name' => $order->store ? $order->store->name : 'NovaBatik Studio',
            'created_at' => $order->created_at ? $order->created_at->format('j M Y, H:i') : date('j M Y, H:i'),
        ];
    }
}
