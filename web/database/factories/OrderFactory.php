<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'order_number' => 'ORD-'.date('Ymd').'-'.strtoupper(fake()->lexify('????')),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'shipping_address' => fake()->address(),
            'total_amount' => fake()->randomNumber(6),
            'status' => 'paid',
            'notes' => null,
        ];
    }
}
