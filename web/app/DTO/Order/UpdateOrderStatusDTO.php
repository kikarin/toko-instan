<?php

namespace App\DTO\Order;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateOrderStatusDTO
{
    public function __construct(
        public string $status,
        public ?string $trackingNumber = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,paid,processing,packed,shipped,completed,cancelled',
            'tracking_number' => 'required_if:status,shipped|nullable|string|max:80',
        ])->validate();

        return new self(
            status: $validated['status'],
            trackingNumber: $validated['tracking_number'] ?? null,
        );
    }
}
