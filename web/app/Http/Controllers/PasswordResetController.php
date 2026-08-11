<?php

namespace App\Http\Controllers;

use App\DTO\Auth\ForgotPasswordDTO;
use App\DTO\Auth\ResetPasswordDTO;
use App\Models\Store;
use App\Repositories\StoreRepository;
use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetController extends Controller
{
    public function __construct(
        protected PasswordResetService $passwordResetService,
        protected StoreRepository $storeRepository
    ) {}

    public function showForgotPassword(?string $storeSlug = null): Response
    {
        $store = $this->resolveStore($storeSlug);

        return Inertia::render('Auth/ForgotPassword', [
            'store' => $store,
        ]);
    }

    public function sendResetLink(Request $request, ?string $storeSlug = null): RedirectResponse
    {
        $store = $this->resolveStore($storeSlug);
        $dto = ForgotPasswordDTO::fromRequest($request);

        $this->passwordResetService->sendResetLink($dto->email, $store?->id, $store?->slug);

        return redirect()->back()->with('status', 'Jika email terdaftar, link reset kata sandi telah dikirim.');
    }

    public function showResetPassword(Request $request): Response
    {
        $storeSlug = $request->route('store_slug');
        $token = $request->route('token');
        $store = $this->resolveStore($storeSlug);

        return Inertia::render('Auth/ResetPassword', [
            'store' => $store,
            'token' => $token,
        ]);
    }

    public function resetPassword(Request $request, ?string $storeSlug = null): RedirectResponse
    {
        $store = $this->resolveStore($storeSlug);
        $dto = ResetPasswordDTO::fromRequest($request);

        $resetted = $this->passwordResetService->reset(
            $dto->email,
            $store?->id,
            $dto->token,
            $dto->password
        );

        if (! $resetted) {
            throw ValidationException::withMessages([
                'email' => 'Link reset tidak valid atau sudah kedaluwarsa.',
            ]);
        }

        return $storeSlug
            ? redirect()->route('store.login', $storeSlug)->with('success', 'Kata sandi berhasil diubah. Silakan masuk.')
            : redirect()->route('login')->with('success', 'Kata sandi berhasil diubah. Silakan masuk.');
    }

    private function resolveStore(?string $storeSlug): ?Store
    {
        if (! $storeSlug) {
            return null;
        }

        $store = $this->storeRepository->findBySlug($storeSlug);

        if (! $store) {
            abort(404);
        }

        return $store;
    }
}
