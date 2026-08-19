<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        $item = OrderItem::factory()->create();

        return [
            'tenant_id' => $item->tenant_id,
            'store_id' => $item->order->store_id,
            'product_id' => $item->product_id,
            'user_id' => User::factory(),
            'order_id' => $item->order_id,
            'order_item_id' => $item->id,
            'rating' => 5,
            'body' => fake()->sentence(),
            'photo_path' => null,
        ];
    }
}
