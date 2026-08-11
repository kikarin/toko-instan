<?php

namespace App\DTO\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResetPasswordDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $token
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'token' => ['required', 'string'],
        ])->validate();

        return new self(
            email: $validated['email'],
            password: $validated['password'],
            token: $validated['token']
        );
    }
}
