<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\StoreRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    public function __construct(protected StoreRepository $storeRepository) {}

    public function index(Request $request): JsonResponse
    {
        $store = $this->storeRepository->getStoreForUser($request->user()->id);
        abort_unless($store, 404);

        $orders = Order::query()
            ->where('store_id', $store->id)
            ->latest('id')
            ->limit(100)
            ->get(['id', 'order_number', 'customer_name', 'customer_email', 'total_amount', 'status', 'created_at']);

        return response()->json(['data' => $orders]);
    }

    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $store = $this->storeRepository->getStoreForUser($request->user()->id);
        abort_unless($store, 404);

        $order = Order::query()
            ->with('items')
            ->where('store_id', $store->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return response()->json(['data' => $order]);
    }
}
