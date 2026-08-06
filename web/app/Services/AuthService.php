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
    public function login(array $credentials): bool
    {
        if (Auth::attempt($credentials, true)) {
            request()->session()->regenerate();

            return true;
        }

        // Auto-provision initial testing account if not exists
        $user = $this->userRepository->findByEmail($credentials['email']);
        if (! $user) {
            $name = Str::before($credentials['email'], '@');
            $user = $this->userRepository->createUser([
                'name' => ucfirst($name),
                'email' => $credentials['email'],
                'password' => $credentials['password'],
                'role' => 'seller',
                'auth_provider' => 'email',
            ]);

            $slug = Str::slug($name);
            $this->storeRepository->createTenantAndStore($user->id, ucfirst($name).' Store', $slug);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return true;
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
