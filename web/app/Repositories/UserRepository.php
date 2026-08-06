<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    /**
     * @return Collection<int, User>
     */
    public function getLatest(int $limit = 100): Collection
    {
        return User::orderByDesc('created_at')->take($limit)->get();
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'seller',
            'auth_provider' => $data['auth_provider'] ?? 'email',
        ]);
    }
}
