<?php

namespace Database\Factories;

use App\Enums\VoucherType;
use App\Models\Store;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'tenant_id' => fn (array $attributes) => Store::query()->find($attributes['store_id'])?->tenant_id,
            'code' => strtoupper(fake()->unique()->bothify('PROMO##')),
            'name' => 'Diskon '.fake()->word(),
            'type' => VoucherType::Nominal,
            'value' => 10000,
            'min_spend' => 0,
            'max_discount' => null,
            'usage_limit' => null,
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ];
    }
}
