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
        public ?string $storeName = null,
        public ?string $storeSlug = null,
        public ?string $referralCode = null,
    ) {}

    public static function fromRequest(Request $request, ?int $storeId = null): self
    {
        $emailRule = ['required', 'string', 'email', 'max:255'];

        if ($storeId) {
            $emailRule[] = Rule::unique('users')->where('store_id', $storeId);
        } else {
            $emailRule[] = Rule::unique('users')->whereNull('store_id');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRule,
            'password' => ['required', 'string', 'min:6'],
            'role' => ['nullable', 'string', 'in:seller,buyer'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'store_slug' => ['nullable', 'string', 'max:255', 'alpha_dash'],
            'referral_code' => ['nullable', 'string', 'max:16'],
        ];

        if ($storeId === null) {
            $rules['store_name'] = ['required', 'string', 'max:255'];
            $rules['store_slug'] = ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('stores', 'slug')];
            $rules['role'] = ['nullable', 'string', 'in:seller'];
        }

        $validated = Validator::make($request->all(), $rules)->validate();

        return new self(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
            role: $storeId ? 'buyer' : 'seller',
            storeName: $validated['store_name'] ?? null,
            storeSlug: $validated['store_slug'] ?? null,
            referralCode: $validated['referral_code'] ?? $request->cookie((string) config('referral.cookie', 'ref_code')),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
            'store_name' => $this->storeName,
            'store_slug' => $this->storeSlug,
        ];
    }
}
