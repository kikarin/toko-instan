<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
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
        $orders = $this->orderService->buyerOrders($request->user())
            ->map(function ($order) {
                return [
                    'order_number' => $order->order_number,
                    'store_name' => $order->store->name ?? 'Official Store',
                    'total_amount' => 'Rp '.number_format($order->total_amount, 0, ',', '.'),
                    'status' => ucfirst($order->status),
                    'created_at' => $order->created_at?->format('d M Y, H:i'),
                ];
            })
            ->values()
            ->toArray();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }
}
