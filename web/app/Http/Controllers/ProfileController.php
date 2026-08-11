<?php

namespace App\Http\Controllers;

use App\DTO\User\ProfileUpdateDTO;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Profile/Edit', [
            'user' => [
                'id' => $user?->id,
                'userId' => '15208'.str_pad((string) ($user?->id ?? 1), 3, '0', STR_PAD_LEFT),
                'name' => $user?->name ?? 'User Toko Instan',
                'username' => $user?->username ?? '',
                'bio' => $user?->bio ?? '',
                'email' => $user?->email,
                'phone' => $user?->phone ?? '+6285264415051',
                'phoneVerified' => ! is_null($user?->phone_verified_at),
                'gender' => $user?->gender ?? 'Pria',
                'birthDate' => $user?->birth_date ?? '01 January 1991',
                'role' => $user?->role ?? 'buyer',
                'avatar' => $user?->avatar,
                'created_at' => $user?->created_at?->format('d M Y'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $dto = ProfileUpdateDTO::fromRequest($request);
        
        $this->userService->updateProfile($request->user(), $dto);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }
}
