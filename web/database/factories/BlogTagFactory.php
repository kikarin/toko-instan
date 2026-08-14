<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogTagFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'store_id' => Store::factory(),
            'tenant_id' => fn (array $a) => Store::query()->find($a['store_id'])?->tenant_id,
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
