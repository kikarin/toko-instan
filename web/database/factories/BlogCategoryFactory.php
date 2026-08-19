<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'store_id' => Store::factory(),
            'tenant_id' => fn (array $a) => Store::query()->find($a['store_id'])?->tenant_id,
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
        ];
    }
}
