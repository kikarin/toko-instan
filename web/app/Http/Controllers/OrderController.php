<?php

namespace App\Http\Controllers;

use App\DTO\Order\UpdateOrderStatusDTO;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected StoreService $storeService,
        protected PaymentService $paymentService,
    ) {}

    public function index(Request $request, ?string $storeSlug = null): Response
    {
        $user = $request->user();

        if ($user->role === 'seller') {
            $store = $this->storeService->getStoreForUser($user->id);

            $sellerOrders = $store
                ? $this->orderService->sellerOrders($store->id)
                    ->map(function ($order) {
                        $payment = $order->payments->sortByDesc('id')->first();

                        return [
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'customer_name' => $order->customer_name,
                            'customer_email' => $order->customer_email,
                            'customer_phone' => $order->customer_phone,
                            'shipping_address' => $order->shipping_address,
                            'shipping_courier' => $order->shipping_courier,
                            'shipping_cost' => (int) ($order->shipping_cost ?? 0),
                            'tracking_number' => $order->tracking_number,
                            'store_name' => $order->store->name ?? 'Toko Resmi',
                            'total_amount' => 'Rp '.number_format($order->total_amount, 0, ',', '.'),
                            'total_num' => (float) $order->total_amount,
                            'status' => strtolower($order->status),
                            'payment_method' => $order->payment_method,
                            'created_at' => $order->created_at?->format('d M Y, H:i'),
                            'notes' => $order->notes,
                            'payment' => $payment ? [
                                'id' => $payment->id,
                                'provider' => $payment->provider->value,
                                'method' => $payment->method->value,
                                'status' => $payment->status->value,
                                'proof_name' => $payment->proof_name,
                                'can_confirm' => in_array($payment->provider->value, ['manual', 'cod'], true)
                                    && $payment->status->value !== 'paid',
                            ] : null,
                            'items' => $order->items->map(fn ($item) => [
                                'id' => $item->id,
                                'product_name' => $item->name,
                                'sku' => $item->sku,
                                'quantity' => $item->qty,
                                'price' => (float) $item->price,
                                'subtotal' => (float) $item->total,
                            ])->values()->all(),
                        ];
                    })
                    ->values()
                    ->toArray()
                : [];

            return Inertia::render('SellerOrders/Index', [
                'orders' => $sellerOrders,
            ]);
        }

        $orders = $this->orderService->buyerOrders($user)
            ->map(function ($order) use ($storeSlug) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'store_name' => $order->store->name ?? 'Toko Resmi',
                    'total_amount' => 'Rp '.number_format($order->total_amount, 0, ',', '.'),
                    'status' => strtolower($order->status),
                    'shipping_courier' => $order->shipping_courier,
                    'tracking_number' => $order->tracking_number,
                    'created_at' => $order->created_at?->format('d M Y, H:i'),
                    'items' => $order->items->map(function ($item) use ($order, $storeSlug) {
                        $isDigital = ($item->product_type ?? 'physical') === 'digital';
                        $canDownload = $isDigital
                            && filled($item->digital_file_path)
                            && in_array($order->status, ['paid', 'processing', 'packed', 'shipped', 'completed'], true);

                        return [
                            'id' => $item->id,
                            'product_name' => $item->name,
                            'sku' => $item->sku,
                            'qty' => $item->qty,
                            'price' => (float) $item->price,
                            'subtotal' => (float) $item->total,
                            'product_type' => $item->product_type ?? 'physical',
                            'product_id' => $item->product_id,
                            'product_slug' => $item->product?->slug,
                            'can_review' => $item->product
                                && in_array($order->status, ['paid', 'processing', 'packed', 'shipped', 'completed'], true)
                                && $item->review === null,
                            'download_url' => $canDownload
                                ? route('orders.digital.download', [
                                    'store_slug' => $storeSlug ?? $order->store?->slug,
                                    'orderItemId' => $item->id,
                                ])
                                : null,
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->toArray();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'storeSlug' => $storeSlug,
        ]);
    }

    public function invoice(Request $request, string $orderNumberOrStoreSlug, ?string $orderNumber = null): Response
    {
        // If $orderNumber is provided, then the route had {store_slug} and {orderNumber}.
        // If not, then the first parameter is actually the {orderNumber} (from seller route).
        $actualOrderNumber = $orderNumber ?? $orderNumberOrStoreSlug;

        $order = $this->orderService->getOrderWithTenant($actualOrderNumber);

        if (! $order) {
            abort(404, 'Pesanan tidak ditemukan');
        }

        // Validate access
        $user = $request->user();
        $isOwner = $user->role === 'seller' && $order->store && $order->store->tenant && $order->store->tenant->user_id === $user->id;
        $isBuyer = $user->role === 'buyer' && $order->customer_email === $user->email;

        if (! $isOwner && ! $isBuyer) {
            abort(403, 'Akses ditolak');
        }

        $invoice = $this->orderService->getInvoiceData($actualOrderNumber);

        return Inertia::render('Order/Invoice', [
            'invoice' => $invoice,
        ]);
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $dto = UpdateOrderStatusDTO::fromRequest($request);
        $order = $this->orderService->getOrder($id);

        $user = $request->user();

        if (! $user->isAdmin()) {
            $store = $this->storeService->getStoreForUser($user->id);

            if (! $store || $order->store_id !== $store->id) {
                abort(403, 'Akses ditolak');
            }
        }

        $this->orderService->updateStatus($order, $dto);

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui!');
    }
}
