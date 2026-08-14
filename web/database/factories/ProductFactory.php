<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
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
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'category' => fake()->randomElement(['Fashion', 'Elektronik', 'Sepatu', 'Aksesoris', 'Kuliner']),
            'price' => fake()->randomNumber(6),
            'sold' => 0,
            'rating' => 4.8,
            'tag' => null,
            'img' => null,
            'stock' => 100,
            'type' => 'physical',
        ];
    }

    public function digital(): static
    {
        return $this->state(fn () => [
            'type' => 'digital',
            'digital_file_path' => 'digital/test/sample.pdf',
            'digital_file_name' => 'sample.pdf',
            'digital_file_mime' => 'application/pdf',
            'weight_gram' => 0,
        ]);
    }
}
