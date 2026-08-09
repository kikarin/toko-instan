<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Store;
use App\Repositories\StoreRepository;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected StoreRepository $storeRepository
    ) {}

    public function index(Request $request, ?string $storeSlug = null): Response
    {
        $user = $request->user();

        if ($user->role === 'seller') {
            $store = Store::whereHas('tenant', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->first();

            $sellerOrders = Order::with(['store', 'items'])
                ->where('store_id', $store?->id ?: 1)
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'customer_name' => $order->customer_name,
                        'customer_email' => $order->customer_email,
                        'customer_phone' => $order->customer_phone,
                        'shipping_address' => $order->shipping_address,
                        'store_name' => $order->store->name ?? 'Toko Resmi',
                        'total_amount' => 'Rp '.number_format($order->total_amount, 0, ',', '.'),
                        'total_num' => (float) $order->total_amount,
                        'status' => strtolower($order->status),
                        'created_at' => $order->created_at?->format('d M Y, H:i'),
                        'notes' => $order->notes,
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
                ->toArray();

            return Inertia::render('SellerOrders/Index', [
                'orders' => $sellerOrders,
            ]);
        }

        $orders = $this->orderService->buyerOrders($user)
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'store_name' => $order->store->name ?? 'Toko Resmi',
                    'total_amount' => 'Rp '.number_format($order->total_amount, 0, ',', '.'),
                    'status' => strtolower($order->status),
                    'created_at' => $order->created_at?->format('d M Y, H:i'),
                    'items' => $order->items->map(fn ($item) => [
                        'product_name' => $item->name,
                        'sku' => $item->sku,
                        'qty' => $item->qty,
                        'price' => (float) $item->price,
                        'subtotal' => (float) $item->total,
                    ])->values()->all(),
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
        
        $order = \App\Models\Order::with('store.tenant')->where('order_number', $actualOrderNumber)->first();

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
        $validated = $request->validate([
            'status' => 'required|string|in:pending,paid,processing,shipped,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);

        match ($validated['status']) {
            'paid' => $this->orderService->markOrderPaid($order),
            'completed' => $this->orderService->markOrderCompleted($order),
            default => $order->update(['status' => $validated['status']]),
        };

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui!');
    }
}
