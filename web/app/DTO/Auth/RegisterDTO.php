<?php

namespace App\DTO\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $role = null,
        public ?string $storeName = null
    ) {}

    public static function fromRequest(Request $request, ?int $storeId = null): self
    {
        $emailRule = ['required', 'string', 'email', 'max:255'];
        
        if ($storeId) {
            $emailRule[] = Rule::unique('users')->where('store_id', $storeId);
        } else {
            $emailRule[] = Rule::unique('users')->whereNull('store_id');
        }

        $validated = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRule,
            'password' => ['required', 'string', 'min:6'],
            'role' => ['nullable', 'string', 'in:seller,buyer'],
            'store_name' => ['nullable', 'string', 'max:255'],
        ])->validate();

        return new self(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
            role: $validated['role'] ?? null,
            storeName: $validated['store_name'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
            'store_name' => $this->storeName,
        ];
    }
}
