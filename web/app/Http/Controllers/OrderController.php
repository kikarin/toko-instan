<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Store;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user->role === 'seller') {
            $store = Store::whereHas('tenant', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->first();

            $sellerOrders = Order::with('store')
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
                        'store_name' => $order->store->name ?? 'Nike Official Store',
                        'total_amount' => 'Rp '.number_format($order->total_amount, 0, ',', '.'),
                        'total_num' => (float) $order->total_amount,
                        'status' => strtolower($order->status),
                        'created_at' => $order->created_at?->format('d M Y, H:i'),
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
                    'store_name' => $order->store->name ?? 'Nike Official Store',
                    'total_amount' => 'Rp '.number_format($order->total_amount, 0, ',', '.'),
                    'status' => strtolower($order->status),
                    'created_at' => $order->created_at?->format('d M Y, H:i'),
                ];
            })
            ->values()
            ->toArray();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
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
