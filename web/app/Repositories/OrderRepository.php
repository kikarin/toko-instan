<?php

namespace App\Repositories;

use App\DTO\CreateOrderDTO;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository
{
    public function getCompletedOrdersSum(): float
    {
        return (float) Order::where('status', 'completed')->sum('total_amount');
    }

    public function countTotalOrders(): int
    {
        return Order::count();
    }

    public function getAverageOrderValue(): float
    {
        $count = $this->countTotalOrders();

        return $count > 0 ? (float) Order::avg('total_amount') : 0;
    }

    public function countByStatus(string $status): int
    {
        return Order::where('status', $status)->count();
    }

    public function createOrder(CreateOrderDTO $dto, string $orderNumber, float $totalAmount): Order
    {
        return Order::create([
            'store_id' => $dto->storeId,
            'order_number' => $orderNumber,
            'customer_name' => $dto->customerName,
            'customer_email' => $dto->customerEmail,
            'customer_phone' => $dto->customerPhone,
            'shipping_address' => $dto->shippingAddress,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'notes' => $dto->notes,
        ]);
    }

    /**
     * @return Collection<int, Order>
     */
    public function getRecent(int $limit = 5)
    {
        return Order::with('store')->orderByDesc('created_at')->take($limit)->get();
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return Order::with('store')->where('order_number', $orderNumber)->first();
    }

    /**
     * @return Collection<int, Order>
     */
    public function getByBuyerEmail(string $email)
    {
        return Order::with('store')
            ->where('customer_email', $email)
            ->orderByDesc('created_at')
            ->get();
    }
}
