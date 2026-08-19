<?php

namespace App\DTO;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateStockDTO
{
    public function __construct(
        public int $stock
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'stock' => ['required', 'integer', 'min:0'],
        ])->validate();

        return new self((int) $validated['stock']);
    }
}
