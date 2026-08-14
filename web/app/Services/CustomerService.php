<?php

namespace App\Services;

use App\Models\Customer;

class CustomerService
{
    public function getCustomersByStore(int $storeId): array
    {
        return Customer::query()
            ->where('store_id', $storeId)
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->withMin('orders', 'created_at')
            ->withMax('orders', 'created_at')
            ->orderByDesc('orders_max_created_at')
            ->get()
            ->map(fn (Customer $customer) => [
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'total_orders' => (int) $customer->orders_count,
                'total_spent' => (float) ($customer->orders_sum_total_amount ?? 0),
                'total_spent_formatted' => 'Rp '.number_format((float) ($customer->orders_sum_total_amount ?? 0), 0, ',', '.'),
                'first_order_at' => optional($customer->orders_min_created_at)->format('d M Y'),
                'last_order_at' => optional($customer->orders_max_created_at)->format('d M Y, H:i'),
            ])
            ->values()
            ->toArray();
    }
}
