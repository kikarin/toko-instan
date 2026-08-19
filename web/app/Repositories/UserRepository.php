<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    /**
     * @return Collection<int, User>
     */
    public function getLatest(int $limit = 100): Collection
    {
        return User::orderByDesc('created_at')->take($limit)->get();
    }

    public function findByEmail(string $email, ?int $storeId = null): ?User
    {
        return User::where('email', $email)
            ->when($storeId !== null, fn ($query) => $query->where('store_id', $storeId))
            ->when($storeId === null, fn ($query) => $query->whereNull('store_id'))
            ->first();
    }

    public function findByEmailGlobal(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function countAll(): int
    {
        return User::count();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByFirebaseUid(string $uid, ?int $storeId = null): ?User
    {
        return User::where('firebase_uid', $uid)->first();
    }

    public function findOrFail(int $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'] ?? str()->random(32),
            'role' => $data['role'] ?? 'seller',
            'store_id' => $data['store_id'] ?? null,
            'auth_provider' => $data['auth_provider'] ?? 'email',
            'firebase_uid' => $data['firebase_uid'] ?? null,
            'avatar' => $data['avatar'] ?? null,
        ]);
    }

    public function updateUser(User $user, array $data): bool
    {
        return $user->update($data);
    }
}
