<?php

namespace App\Services;

use App\Mail\ResetPasswordMail;
use App\Repositories\PasswordResetRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected PasswordResetRepository $passwordResetRepository
    ) {}

    /**
     * Generate a store-scoped reset token and send the reset email.
     *
     * Respond generically even when the email is unknown to avoid user
     * enumeration. The token is only stored for an account that exists.
     */
    public function sendResetLink(string $email, ?int $storeId, ?string $storeSlug): void
    {
        $user = $this->userRepository->findByEmail($email, $storeId);

        if (! $user) {
            return;
        }

        $token = Str::random(60);
        $this->passwordResetRepository->store($email, $storeId, $token);

        $resetUrl = $storeSlug
            ? route('store.password.reset', ['store_slug' => $storeSlug, 'token' => $token])
            : route('password.reset', ['token' => $token]);

        Mail::to($email)->queue(new ResetPasswordMail($resetUrl));
    }

    /**
     * Verify the store-scoped token and update the user's password.
     */
    public function reset(string $email, ?int $storeId, string $token, string $password): bool
    {
        $record = $this->passwordResetRepository->findValid($email, $storeId, $token);

        if (! $record) {
            return false;
        }

        $user = $this->userRepository->findByEmail($email, $storeId);

        if (! $user) {
            return false;
        }

        $user->forceFill([
            'password' => Hash::make($password),
            'auth_provider' => 'email',
        ])->save();

        $this->passwordResetRepository->deleteForEmail($email, $storeId);

        return true;
    }
}
