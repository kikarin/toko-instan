<?php

namespace App\Services;

use App\DTO\User\ProfileUpdateDTO;
use App\Models\User;
use App\Repositories\UserRepository;

class UserService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function updateProfile(User $user, ProfileUpdateDTO $dto): User
    {
        $data = $dto->validatedData;
        
        $updateData = [
            'name' => $data['name'],
            'username' => $data['username'] ?? $user->username,
            'bio' => $data['bio'] ?? $user->bio,
            'phone' => $data['phone'] ?? $user->phone,
            'gender' => $data['gender'] ?? $user->gender,
            'birth_date' => $data['birth_date'] ?? $user->birth_date,
            'avatar' => $data['avatar'] ?? $user->avatar,
        ];

        $this->userRepository->updateUser($user, $updateData);

        return $user;
    }
}
