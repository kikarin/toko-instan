<?php

namespace App\Http\Controllers;

use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeveloperController extends Controller
{
    public function __construct(protected StoreService $storeService) {}

    public function index(Request $request): Response
    {
        $tokens = $request->user()->tokens()->latest()->get()->map(fn ($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'last_used_at' => $t->last_used_at?->format('d M Y H:i'),
            'created_at' => $t->created_at?->format('d M Y'),
        ]);

        return Inertia::render('Developer/Index', [
            'tokens' => $tokens,
            'plain_token' => $request->session()->get('plain_api_token'),
            'docs_url' => url('/api/docs'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        $new = $request->user()->createToken($validated['name']);

        return back()->with('plain_api_token', $new->plainTextToken)
            ->with('success', 'API key dibuat. Salin sekarang, tidak ditampilkan ulang.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $request->user()->tokens()->whereKey($id)->delete();

        return back()->with('success', 'API key dicabut.');
    }
}
