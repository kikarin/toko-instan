<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Repositories\StoreRepository;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected StoreRepository $storeRepository
    ) {}

    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login', [
            'intent' => 'platform',
        ]);
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
        return Inertia::render('Auth/Register', [
            'intent' => 'platform',
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users')->whereNull('store_id'),
            ],
            'password' => ['required', 'string', 'min:6'],
            'store_name' => ['required', 'string', 'max:255'],
            'store_slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('stores', 'slug')],
        ]);

        $validated['role'] = 'seller';

        $this->authService->register($validated);

        $user = $request->user();

        return redirect()->intended($user?->homePath() ?? '/');
    }

    public function google(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
            'intent' => ['nullable', 'string', 'in:login,register'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'store_slug' => ['nullable', 'string', 'max:255', 'alpha_dash'],
            'store_slug_context' => ['nullable', 'string', 'max:255'],
        ]);

        $storeId = null;
        if (! empty($validated['store_slug_context'])) {
            $store = $this->storeRepository->findBySlug($validated['store_slug_context']);
            if (! $store) {
                abort(404);
            }
            $storeId = $store->id;
        }

        if (($validated['intent'] ?? 'login') === 'register' && $storeId === null) {
            $request->validate([
                'store_name' => ['required', 'string', 'max:255'],
                'store_slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('stores', 'slug')],
            ]);
        }

        $user = $this->authService->loginWithGoogle([
            'id_token' => $validated['id_token'],
            'intent' => $validated['intent'] ?? 'login',
            'store_name' => $validated['store_name'] ?? null,
            'store_slug' => $validated['store_slug'] ?? null,
            'store_id' => $storeId,
        ]);

        return redirect()->intended($user->homePath() ?? '/');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();
        $storeSlug = null;

        if ($user && $user->store_id) {
            $store = Store::find($user->store_id);
            if ($store) {
                $storeSlug = $store->slug;
            }
        }

        $this->authService->logout();

        if ($storeSlug) {
            return redirect('/'.$storeSlug);
        }

        return redirect('/login');
    }

    public function showStoreLogin(string $storeSlug): Response
    {
        $store = $this->storeRepository->findBySlug($storeSlug);
        if (! $store) {
            abort(404);
        }

        return Inertia::render('Auth/Login', [
            'intent' => 'storefront',
            'store' => $store,
        ]);
    }

    public function storeLogin(Request $request, string $storeSlug): RedirectResponse
    {
        $store = $this->storeRepository->findBySlug($storeSlug);
        if (! $store) {
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
        if (! $store) {
            abort(404);
        }

        return Inertia::render('Auth/Register', [
            'intent' => 'storefront',
            'store' => $store,
        ]);
    }

    public function storeRegister(Request $request, string $storeSlug): RedirectResponse
    {
        $store = $this->storeRepository->findBySlug($storeSlug);
        if (! $store) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users')->where('store_id', $store->id),
            ],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $validated['role'] = 'buyer';
        $validated['store_id'] = $store->id;

        $this->authService->register($validated);

        $user = $request->user();

        return redirect()->intended($user?->homePath() ?? '/');
    }
}
