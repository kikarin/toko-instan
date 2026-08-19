<?php

namespace App\DTO\Catalog;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryDTO
{
    public function __construct(public string $name) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
        ])->validate();

        return new self($validated['name']);
    }
}
