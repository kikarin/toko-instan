<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $store = Store::whereHas('tenant', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->first();

        $storeId = $store?->id;

        $customers = [];

        if ($storeId) {
            $customers = Order::where('store_id', $storeId)
                ->select('customer_name', 'customer_email', 'customer_phone')
                ->selectRaw('COUNT(*) as total_orders')
                ->selectRaw('SUM(total_amount) as total_spent')
                ->selectRaw('MIN(created_at) as first_order_at')
                ->selectRaw('MAX(created_at) as last_order_at')
                ->groupBy('customer_email', 'customer_name', 'customer_phone')
                ->orderByDesc('last_order_at')
                ->get()
                ->map(fn ($row) => [
                    'name' => $row->customer_name,
                    'email' => $row->customer_email,
                    'phone' => $row->customer_phone,
                    'total_orders' => (int) $row->total_orders,
                    'total_spent' => (float) $row->total_spent,
                    'total_spent_formatted' => 'Rp '.number_format($row->total_spent, 0, ',', '.'),
                    'first_order_at' => optional($row->first_order_at)?->format('d M Y'),
                    'last_order_at' => optional($row->last_order_at)?->format('d M Y, H:i'),
                ])
                ->values()
                ->toArray();
        }

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'storeName' => $store?->name ?? 'Toko Anda',
        ]);
    }
}
