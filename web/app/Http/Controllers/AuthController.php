<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
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

        $this->authService->login($credentials);

        $user = $request->user();
        if ($user && $user->role === 'buyer') {
            return redirect()->intended('/marketplace');
        }

        return redirect()->intended('/');
    }

    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['nullable', 'string', 'in:seller,buyer'],
            'store_name' => ['nullable', 'string', 'max:255'],
        ]);

        $this->authService->register($validated);

        $role = $validated['role'] ?? 'seller';
        if ($role === 'buyer') {
            return redirect()->intended('/marketplace');
        }

        return redirect()->intended('/');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout();

        return redirect('/login');
    }
}
