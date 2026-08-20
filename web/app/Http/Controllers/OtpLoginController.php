<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Repositories\StoreRepository;
use App\Services\OtpLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OtpLoginController extends Controller
{
    public function __construct(
        protected OtpLoginService $otpLoginService,
        protected StoreRepository $storeRepository
    ) {}

    public function show(?string $storeSlug = null): Response
    {
        $store = $this->resolveStore($storeSlug);

        return Inertia::render('Auth/OtpLogin', [
            'store' => $store,
            'intent' => $store ? 'storefront' : 'platform',
            'status' => session('status'),
        ]);
    }

    public function send(Request $request, ?string $storeSlug = null): RedirectResponse
    {
        $store = $this->resolveStore($storeSlug);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $this->otpLoginService->sendCode($validated['email'], $store?->id);

        return back()->with('status', 'Jika email terdaftar, kode OTP telah dikirim.');
    }

    public function verify(Request $request, ?string $storeSlug = null): RedirectResponse
    {
        $store = $this->resolveStore($storeSlug);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $this->otpLoginService->verifyAndLogin(
            $validated['email'],
            $validated['code'],
            $store?->id
        );

        return redirect()->intended($user->homePath());
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
