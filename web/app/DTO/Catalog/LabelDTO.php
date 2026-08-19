<?php

namespace App\DTO\Catalog;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LabelDTO
{
    public function __construct(
        public string $name,
        public ?string $color = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'color' => 'nullable|string|max:30',
        ])->validate();

        return new self(
            name: $validated['name'],
            color: $validated['color'] ?? null
        );
    }
}
