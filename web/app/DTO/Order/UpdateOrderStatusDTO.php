<?php

namespace App\DTO\Order;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateOrderStatusDTO
{
    public function __construct(
        public string $status
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,paid,processing,shipped,completed,cancelled',
        ])->validate();

        return new self(
            status: $validated['status']
        );
    }
}
