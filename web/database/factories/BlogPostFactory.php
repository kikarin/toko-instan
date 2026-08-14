<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogPostFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'store_id' => Store::factory(),
            'tenant_id' => fn (array $a) => Store::query()->find($a['store_id'])?->tenant_id,
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('##'),
            'excerpt' => fake()->sentence(),
            'body' => fake()->paragraphs(3, true),
            'is_published' => true,
            'published_at' => now()->subDay(),
        ];
    }
}
