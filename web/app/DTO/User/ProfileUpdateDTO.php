<?php

namespace App\DTO\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileUpdateDTO
{
    public function __construct(
        public array $validatedData
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50'],
            'bio' => ['nullable', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        return new self($validated);
    }
}
