<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
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
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50'],
            'bio' => ['nullable', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'string', 'max:1000'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'username' => $validated['username'] ?? $user->username,
            'bio' => $validated['bio'] ?? $user->bio,
            'phone' => $validated['phone'] ?? $user->phone,
            'gender' => $validated['gender'] ?? $user->gender,
            'birth_date' => $validated['birth_date'] ?? $user->birth_date,
            'avatar' => $validated['avatar'] ?? $user->avatar,
        ]);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }
}
