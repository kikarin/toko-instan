<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'tenant_id' => null,
            'provider' => PaymentProvider::Midtrans,
            'method' => PaymentMethod::Qris,
            'amount' => 100000,
            'status' => PaymentStatus::Pending,
            'external_id' => null,
            'idempotency_key' => (string) Str::uuid(),
            'snap_token' => null,
            'redirect_url' => null,
            'payload' => null,
            'paid_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function manual(): static
    {
        return $this->state(fn () => [
            'provider' => PaymentProvider::Manual,
            'method' => PaymentMethod::Transfer,
        ]);
    }

    public function cod(): static
    {
        return $this->state(fn () => [
            'provider' => PaymentProvider::Cod,
            'method' => PaymentMethod::Cod,
        ]);
    }
}
