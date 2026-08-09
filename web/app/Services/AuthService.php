<?php

namespace App\Services;

use App\Repositories\StoreRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected StoreRepository $storeRepository
    ) {}

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function login(array $credentials, ?int $storeId = null): bool
    {
        $buyerCredentials = $credentials;
        $buyerCredentials['store_id'] = $storeId;

        if (Auth::attempt($buyerCredentials, true)) {
            request()->session()->regenerate();
            return true;
        }

        if ($storeId !== null) {
            $globalCredentials = $credentials;
            $globalCredentials['store_id'] = null;

            $user = $this->userRepository->findByEmail($credentials['email'], null);
                
            if ($user && in_array($user->role, ['seller', 'admin'])) {
                if (Auth::attempt($globalCredentials, true)) {
                    request()->session()->regenerate();
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function register(array $data): bool
    {
        $role = $data['role'] ?? ($data['store_name'] ? 'seller' : 'buyer');

        $user = $this->userRepository->createUser([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $role,
            'store_id' => $data['store_id'] ?? null,
            'auth_provider' => 'email',
        ]);

        if ($role === 'seller' && ! empty($data['store_name'])) {
            $slug = Str::slug($data['store_name']);
            $this->storeRepository->createTenantAndStore($user->id, $data['store_name'], $slug);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return true;
    }

    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
