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
        protected WalletService $walletService,
        protected SubscriptionService $subscriptionService,
        protected ShippingService $shippingService,
        protected VoucherService $voucherService,
        protected TaxService $taxService,
        protected SellerAlertService $sellerAlertService,
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
            $allDigital = true;
            $storeId = null;

            foreach ($dto->items as $item) {
                $product = $this->productRepository->find((int) $item['id']);

                if (! $product) {
                    throw new \RuntimeException('Produk tidak ditemukan.');
                }

                $storeId ??= (int) $product->store_id;

                if (! $product->is_active) {
                    throw new \RuntimeException("Produk '{$product->name}' sedang tidak aktif.");
                }

                if (! $product->isDigital()) {
                    $allDigital = false;
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

            $store = $storeId ? $this->storeRepository->findById($storeId) : null;

            $shippingFee = 0;
            $shippingService = $dto->shippingService;
            $discount = 0;
            $voucherId = null;
            $voucherCode = null;
            $appliedVoucher = null;

            if (! $allDigital && $store) {
                $shippingFee = $this->shippingService->resolveCost(
                    $store,
                    (string) ($dto->destinationCity ?? ''),
                    $dto->items,
                    $dto->shippingRateId,
                    $dto->shippingCourier,
                    $dto->destinationPostalCode,
                );
                $shippingService = $dto->shippingService;
            }

            if ($store && filled($dto->voucherCode)) {
                $preview = $this->voucherService->preview($store, $dto->voucherCode, (int) round($subtotal));
                $discount = $preview['discount'];
                $appliedVoucher = $preview['voucher'];
                $voucherId = $appliedVoucher->id;
                $voucherCode = $appliedVoucher->code;
            }

            $dpp = max(0, (int) round($subtotal) - $discount);
            $tax = $this->taxService->ppnAmount($store, $dpp);
            $totalAmount = $dpp + $shippingFee + $tax;

            $orderNumber = 'ORD-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $order = $this->orderRepository->createOrder(
                $dto,
                $orderNumber,
                $totalAmount,
                $shippingFee,
                $shippingService,
                $discount,
                $tax,
                $voucherId,
                $voucherCode,
            );

            if ($appliedVoucher) {
                $this->voucherService->redeem($appliedVoucher);
            }

            $this->storeRepository->incrementTotalOrders($order->store_id);

            $this->sellerAlertService->notifyNewOrder($order->load('store.tenant.user'));

            return $order;
        });
    }

    public function markOrderPaid(Order $order): void
    {
        if ($order->status === 'paid' || $order->status === 'completed') {
            return;
        }

        $tenant = $order->store?->tenant;
        $tenantId = $tenant?->id;
        if ($tenantId === null) {
            throw new \RuntimeException('Order tanpa store tidak bisa diproses escrow.');
        }

        $wallet = $this->walletService->ensureForTenant($tenantId);

        if ($this->subscriptionService->usesDirectSettlement($tenant)) {
            $this->walletService->creditOrderDirect(
                $wallet->id,
                (float) $order->total_amount,
                $order->id,
                'Settlement langsung (order '.$order->order_number.')'
            );
        } else {
            $this->walletService->creditOrderEscrow(
                $wallet->id,
                (float) $order->total_amount,
                $order->id,
                'Escrow penjualan (order '.$order->order_number.')'
            );
        }

        $order->update([
            'status' => 'paid',
            'paid_at' => $order->paid_at ?? now(),
        ]);
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
            'packed' => $order->update([
                'status' => 'packed',
                'packed_at' => $order->packed_at ?? now(),
            ]),
            'shipped' => $order->update([
                'status' => 'shipped',
                'tracking_number' => $dto->trackingNumber,
                'shipped_at' => $order->shipped_at ?? now(),
            ]),
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
        $shippingFee = (float) ($order->shipping_cost ?? 0);
        $discount = (float) ($order->discount ?? 0);
        $tax = (float) ($order->tax ?? 0);

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
            'discount' => $discount,
            'discount_formatted' => 'Rp '.number_format($discount, 0, ',', '.'),
            'voucher_code' => $order->voucher_code,
            'tax' => $tax,
            'tax_formatted' => 'Rp '.number_format($tax, 0, ',', '.'),
            'shipping_fee' => $shippingFee,
            'shipping_fee_formatted' => 'Rp '.number_format($shippingFee, 0, ',', '.'),
            'shipping_courier' => $order->shipping_courier,
            'tracking_number' => $order->tracking_number,
            'total_amount' => 'Rp '.number_format($total, 0, ',', '.'),
            'total_num' => $total,
            'status' => ucfirst($order->status),
            'store_name' => $order->store ? $order->store->name : 'NovaBatik Studio',
            'created_at' => $order->created_at ? $order->created_at->format('j M Y, H:i') : date('j M Y, H:i'),
            'items' => $items,
        ];
    }
}
