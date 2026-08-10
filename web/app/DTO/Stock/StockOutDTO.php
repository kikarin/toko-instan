<?php

namespace App\DTO\Stock;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockOutDTO
{
    public function __construct(
        public int $quantity,
        public ?string $reason
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ])->validate();

        return new self(
            quantity: (int) $validated['quantity'],
            reason: $validated['reason'] ?? null
        );
    }
}
