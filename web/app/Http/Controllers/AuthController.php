<?php

namespace App\Http\Controllers;

use App\Repositories\StoreRepository;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
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
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $dto = LoginDTO::fromRequest($request);

        if (! $this->authService->login($dto)) {
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
        $dto = RegisterDTO::fromRequest($request);
        $this->authService->register($dto);

        $user = $request->user();

        return redirect()->intended($user?->homePath() ?? '/');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();
        $storeSlug = null;

        if ($user && $user->store_id) {
            $store = $this->storeRepository->findById($user->store_id);
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
            'store' => $store,
        ]);
    }

    public function storeLogin(Request $request, string $storeSlug): RedirectResponse
    {
        $store = $this->storeRepository->findBySlug($storeSlug);
        if (! $store) {
            abort(404);
        }

        $dto = LoginDTO::fromRequest($request);

        if (! $this->authService->login($dto, $store->id)) {
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
            'store' => $store,
        ]);
    }

    public function storeRegister(Request $request, string $storeSlug): RedirectResponse
    {
        $store = $this->storeRepository->findBySlug($storeSlug);
        if (! $store) {
            abort(404);
        }

        $dto = RegisterDTO::fromRequest($request, $store->id);
        $this->authService->register($dto, $store->id);

        $user = $request->user();

        return redirect()->intended($user?->homePath() ?? '/');
    }
}
