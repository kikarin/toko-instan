<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Settings', [
            'user' => [
                'id' => $user?->id,
                'name' => $user?->name ?? 'User Toko Instan',
                'email' => $user?->email,
                'role' => $user?->role ?? 'buyer',
                'avatar' => $user?->avatar,
            ],
        ]);
    }
}
