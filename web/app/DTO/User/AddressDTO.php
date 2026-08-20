<?php

namespace App\DTO\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressDTO
{
    public function __construct(
        public array $validatedData
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'label' => 'nullable|string|max:60',
            'recipient_name' => 'required|string|max:120',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'is_default' => 'nullable|boolean',
        ])->validate();

        return new self($validated);
    }
}
