<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use Illuminate\Support\Collection;

class CustomerService
{
    public function __construct(protected OrderRepository $orderRepository) {}

    public function getCustomersByStore(int $storeId): array
    {
        return $this->orderRepository->getCustomersByStore($storeId)
            ->map(fn ($row) => [
                'name' => $row->customer_name,
                'email' => $row->customer_email,
                'phone' => $row->customer_phone,
                'total_orders' => (int) $row->total_orders,
                'total_spent' => (float) $row->total_spent,
                'total_spent_formatted' => 'Rp '.number_format($row->total_spent, 0, ',', '.'),
                'first_order_at' => optional($row->first_order_at)->format('d M Y'),
                'last_order_at' => optional($row->last_order_at)->format('d M Y, H:i'),
            ])
            ->values()
            ->toArray();
    }
}
