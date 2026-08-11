<?php

namespace App\DTO\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordDTO
{
    public function __construct(
        public string $email
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
        ])->validate();

        return new self(
            email: $validated['email']
        );
    }
}
