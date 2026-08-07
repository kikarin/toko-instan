<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'balance' => 0,
            'pending_balance' => 0,
            'currency' => 'IDR',
        ];
    }
}
