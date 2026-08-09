<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected \App\Repositories\StoreRepository $storeRepository
    ) {}

    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! $this->authService->login($credentials)) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = $request->user();

        return redirect()->intended($user?->homePath() ?? '/');
    }

    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                \Illuminate\Validation\Rule::unique('users')->whereNull('store_id')
            ],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['nullable', 'string', 'in:seller,buyer'],
            'store_name' => ['nullable', 'string', 'max:255'],
        ]);

        $this->authService->register($validated);

        $user = $request->user();

        return redirect()->intended($user?->homePath() ?? '/');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();
        $storeSlug = null;

        if ($user && $user->store_id) {
            $store = \App\Models\Store::find($user->store_id);
            if ($store) {
                $storeSlug = $store->slug;
            }
        }

        $this->authService->logout();

        if ($storeSlug) {
            return redirect('/' . $storeSlug);
        }

        return redirect('/login');
    }

    public function showStoreLogin(string $storeSlug): Response
    {
        $store = $this->storeRepository->findBySlug($storeSlug);
        if (!$store) {
            abort(404);
        }

        return Inertia::render('Auth/Login', [
            'store' => $store
        ]);
    }

    public function storeLogin(Request $request, string $storeSlug): RedirectResponse
    {
        $store = $this->storeRepository->findBySlug($storeSlug);
        if (!$store) {
            abort(404);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! $this->authService->login($credentials, $store->id)) {
            throw ValidationException::withMessages([
                'email' => 'Akun belum terdaftar di toko ini atau kata sandi salah.',
            ]);
        }

        $user = $request->user();

        return redirect()->intended($user?->homePath() ?? '/');
    }

    public function showStoreRegister(string $storeSlug): Response
    {
        $store = $this->storeRepository->findBySlug($storeSlug);
        if (!$store) {
            abort(404);
        }

        return Inertia::render('Auth/Register', [
            'store' => $store
        ]);
    }

    public function storeRegister(Request $request, string $storeSlug): RedirectResponse
    {
        $store = $this->storeRepository->findBySlug($storeSlug);
        if (!$store) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                \Illuminate\Validation\Rule::unique('users')->where('store_id', $store->id)
            ],
            'password' => ['required', 'string', 'min:6'],
        ]);

        // Force role to buyer and inject store_id
        $validated['role'] = 'buyer';
        $validated['store_id'] = $store->id;

        $this->authService->register($validated);

        $user = $request->user();

        return redirect()->intended($user?->homePath() ?? '/');
    }
}
