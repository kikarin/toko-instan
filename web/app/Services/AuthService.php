<?php

namespace App\Services;

use App\Actions\CreateTenantAndStore;
use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Models\User;
use App\Repositories\StoreRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AuthService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected CreateTenantAndStore $createTenantAndStore,
        protected FirebaseAuthService $firebaseAuthService,
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

            if ($user && in_array($user->role, ['seller', 'admin'], true)) {
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
        $role = $storeId ? 'buyer' : 'seller';

        $user = $this->userRepository->createUser([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password,
            'role' => $role,
            'store_id' => $storeId,
            'auth_provider' => 'email',
        ]);

        if ($role === 'seller' && filled($dto->storeName)) {
            $slug = $this->resolveStoreSlug([
                'store_slug' => $dto->storeSlug,
                'store_name' => $dto->storeName,
            ]);
            ($this->createTenantAndStore)($user->id, $dto->storeName, $slug);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return true;
    }

    /**
     * Verify Firebase ID token and create/login the matching Laravel user.
     *
     * @param  array{id_token: string, store_name?: string|null, store_slug?: string|null, store_id?: int|null, intent?: string|null}  $data
     */
    public function loginWithGoogle(array $data): User
    {
        try {
            $identity = $this->firebaseAuthService->verifyIdToken($data['id_token']);
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages([
                'id_token' => $e->getMessage() ?: 'Token Google tidak valid. Silakan coba lagi.',
            ]);
        }

        if (empty($identity['email'])) {
            throw ValidationException::withMessages([
                'id_token' => 'Akun Google tidak menyediakan email.',
            ]);
        }

        $storeId = $data['store_id'] ?? null;
        $intent = $data['intent'] ?? 'login';

        $user = $this->userRepository->findByFirebaseUid($identity['uid'], $storeId)
            ?? $this->userRepository->findByEmail($identity['email'], $storeId);

        if ($user === null && $intent === 'register') {
            $role = $storeId ? 'buyer' : 'seller';

            if ($role === 'seller' && empty($data['store_name'])) {
                throw ValidationException::withMessages([
                    'store_name' => 'Nama toko wajib diisi untuk daftar seller.',
                ]);
            }

            $user = $this->userRepository->createUser([
                'name' => $identity['name'] ?: strstr($identity['email'], '@', true) ?: 'User',
                'email' => $identity['email'],
                'role' => $role,
                'store_id' => $storeId,
                'auth_provider' => 'google',
                'firebase_uid' => $identity['uid'],
                'avatar' => $identity['picture'],
            ]);

            if ($role === 'seller') {
                $slug = $this->resolveStoreSlug($data);
                ($this->createTenantAndStore)($user->id, (string) $data['store_name'], $slug);
            }
        }

        if ($user === null) {
            throw ValidationException::withMessages([
                'id_token' => 'Akun belum terdaftar. Silakan daftar terlebih dahulu.',
            ]);
        }

        if ($user->firebase_uid !== $identity['uid']) {
            $user->forceFill([
                'firebase_uid' => $identity['uid'],
                'auth_provider' => 'google',
                'avatar' => $identity['picture'] ?: $user->avatar,
            ])->save();
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return $user;
    }

    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveStoreSlug(array $data): string
    {
        $slug = Str::slug((string) ($data['store_slug'] ?? $data['store_name'] ?? ''));

        if ($slug === '') {
            $slug = 'toko-'.Str::lower(Str::random(6));
        }

        $base = $slug;
        $i = 1;
        while ($this->storeRepository->findBySlug($slug)) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
