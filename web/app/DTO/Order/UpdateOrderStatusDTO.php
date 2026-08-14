<?php

namespace App\DTO\Order;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateOrderStatusDTO
{
    public function __construct(
        public string $status,
        public ?string $trackingNumber = null,
        public ?string $trackingCourier = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,paid,processing,shipped,completed,cancelled',
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'tracking_courier' => ['nullable', 'string', 'max:100'],
        ])->validate();

        return new self(
            status: $validated['status'],
            trackingNumber: $validated['tracking_number'] ?? null,
            trackingCourier: $validated['tracking_courier'] ?? null
        );
    }
}
