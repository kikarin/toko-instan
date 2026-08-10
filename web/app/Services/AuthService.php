<?php

namespace App\Services;

use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
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

    public function login(LoginDTO $dto, ?int $storeId = null): bool
    {
        $buyerCredentials = $dto->toArray();
        $buyerCredentials['store_id'] = $storeId;

        if (Auth::attempt($buyerCredentials, true)) {
            request()->session()->regenerate();

            return true;
        }

        if ($storeId !== null) {
            $globalCredentials = $dto->toArray();
            $globalCredentials['store_id'] = null;

            $user = $this->userRepository->findByEmail($dto->email, null);

            if ($user && in_array($user->role, ['seller', 'admin'])) {
                if (Auth::attempt($globalCredentials, true)) {
                    request()->session()->regenerate();

                    return true;
                }
            }
        }

        return false;
    }

    public function register(RegisterDTO $dto, ?int $storeId = null): bool
    {
        $role = $storeId ? 'buyer' : ($dto->role ?? ($dto->storeName ? 'seller' : 'buyer'));

        $user = $this->userRepository->createUser([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password,
            'role' => $role,
            'store_id' => $storeId,
            'auth_provider' => 'email',
        ]);

        if ($role === 'seller' && ! empty($dto->storeName)) {
            $slug = Str::slug($dto->storeName);
            $this->storeRepository->createTenantAndStore($user->id, $dto->storeName, $slug);
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
