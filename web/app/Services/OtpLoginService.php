<?php

namespace App\Services;

use App\Mail\OtpLoginMail;
use App\Models\User;
use App\Repositories\LoginOtpRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class OtpLoginService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected LoginOtpRepository $loginOtpRepository
    ) {}

    /**
     * Send a one-time login code to email. Always responds generically to callers.
     */
    public function sendCode(string $email, ?int $storeId): void
    {
        $user = $this->resolveUser($email, $storeId);

        if (! $user) {
            return;
        }

        $code = (string) random_int(100000, 999999);
        $this->loginOtpRepository->store($email, $storeId, $code);

        Mail::to($email)->queue(new OtpLoginMail($code));
    }

    public function verifyAndLogin(string $email, string $code, ?int $storeId): User
    {
        $record = $this->loginOtpRepository->findValid($email, $storeId, $code);

        if (! $record) {
            throw ValidationException::withMessages([
                'code' => 'Kode OTP tidak valid atau sudah kedaluwarsa.',
            ]);
        }

        $user = $this->resolveUser($email, $storeId);

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'Akun tidak ditemukan.',
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $this->loginOtpRepository->deleteForEmail($email, $storeId);

        Auth::login($user, true);
        request()->session()->regenerate();

        return $user->fresh();
    }

    protected function resolveUser(string $email, ?int $storeId): ?User
    {
        $user = $this->userRepository->findByEmail($email, $storeId);

        if ($user) {
            return $user;
        }

        if ($storeId === null) {
            return null;
        }

        $global = $this->userRepository->findByEmail($email, null);

        if ($global && in_array($global->role, ['seller', 'admin'], true)) {
            return $global;
        }

        return null;
    }
}
