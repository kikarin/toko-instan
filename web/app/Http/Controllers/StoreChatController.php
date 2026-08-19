<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Services\StoreChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreChatController extends Controller
{
    public function __construct(protected StoreChatService $chatService) {}

    public function show(Request $request, string $storeSlug): JsonResponse
    {
        $store = Store::query()->where('slug', $storeSlug)->firstOrFail();
        $chat = $this->chatService->thread($store, $this->sessionKey($request));

        return response()->json(['messages' => $this->chatService->messages($chat)]);
    }

    public function store(Request $request, string $storeSlug): JsonResponse
    {
        $store = Store::query()->where('slug', $storeSlug)->firstOrFail();
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
            'visitor_name' => ['nullable', 'string', 'max:80'],
        ]);

        $messages = $this->chatService->ask(
            $store,
            $this->sessionKey($request),
            $validated['body'],
            $validated['visitor_name'] ?? null,
        );

        return response()->json(['messages' => $messages]);
    }

    protected function sessionKey(Request $request): string
    {
        $existing = (string) $request->cookie('store_chat_session', '');
        if ($existing !== '') {
            return $existing;
        }

        $key = (string) $request->session()->get('store_chat_session');
        if (! is_string($key) || $key === '') {
            $key = Str::uuid()->toString();
            $request->session()->put('store_chat_session', $key);
        }

        return $key;
    }
}
