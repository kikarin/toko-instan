<?php

namespace App\DTO\Stock;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockAdjustDTO
{
    public function __construct(
        public int $newStock,
        public ?string $reason
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'new_stock' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:255',
        ])->validate();

        return new self(
            newStock: (int) $validated['new_stock'],
            reason: $validated['reason'] ?? null
        );
    }
}
