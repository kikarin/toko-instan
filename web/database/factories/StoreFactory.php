<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->company().' Store',
            'slug' => fake()->unique()->slug(),
            'category' => 'Fashion',
            'description' => fake()->sentence(),
            'logo' => null,
            'status' => 'active',
            'balance' => fake()->randomNumber(7),
            'pending_escrow' => fake()->randomNumber(6),
            'gmv' => fake()->randomNumber(8),
            'total_orders' => fake()->randomNumber(3),
            'rating' => fake()->randomFloat(1, 3.5, 5),
            'badge' => null,
            'avatar_hue' => 220,
        ];
    }
}
