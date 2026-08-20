<?php

namespace App\DTO\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ])->validate();

        return new self(
            email: $validated['email'],
            password: $validated['password']
        );
    }
    
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
