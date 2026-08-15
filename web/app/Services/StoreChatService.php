<?php

namespace App\Services;

use App\Contracts\AiProvider;
use App\Models\Store;
use App\Models\StoreChat;
use App\Models\StoreChatMessage;
use Throwable;

class StoreChatService
{
    public function __construct(protected AiProvider $aiProvider) {}

    public function thread(Store $store, string $sessionKey): StoreChat
    {
        return StoreChat::query()->firstOrCreate(
            ['store_id' => $store->id, 'session_key' => $sessionKey],
            ['tenant_id' => $store->tenant_id],
        );
    }

    /**
     * @return list<array{role: string, body: string, created_at: ?string}>
     */
    public function messages(StoreChat $chat): array
    {
        return $chat->messages()->orderBy('id')->get()->map(fn (StoreChatMessage $m) => [
            'role' => $m->role,
            'body' => $m->body,
            'created_at' => $m->created_at?->toIso8601String(),
        ])->all();
    }

    /**
     * @return list<array{role: string, body: string, created_at: ?string}>
     */
    public function ask(Store $store, string $sessionKey, string $body, ?string $visitorName = null): array
    {
        $chat = $this->thread($store, $sessionKey);
        if ($visitorName) {
            $chat->update(['visitor_name' => $visitorName]);
        }

        $chat->messages()->create(['role' => 'visitor', 'body' => $body]);
        $reply = $this->autoReply($store, $body);
        $chat->messages()->create(['role' => 'assistant', 'body' => $reply]);

        return $this->messages($chat->fresh());
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listForStore(Store $store): array
    {
        return StoreChat::query()
            ->where('store_id', $store->id)
            ->with(['messages' => fn ($q) => $q->orderByDesc('id')->limit(1)])
            ->latest('updated_at')
            ->limit(50)
            ->get()
            ->map(fn (StoreChat $c) => [
                'id' => $c->id,
                'session_key' => $c->session_key,
                'visitor_name' => $c->visitor_name ?: 'Pengunjung',
                'last_message' => $c->messages->first()?->body,
                'updated_at' => $c->updated_at?->format('d M Y H:i'),
            ])
            ->all();
    }

    protected function autoReply(Store $store, string $message): string
    {
        $fallback = "Halo, terima kasih sudah menghubungi {$store->name}. Tim kami akan segera membalas. Sementara itu silakan cek etalase toko ya.";

        if (config('ai.driver', 'fake') === 'fake') {
            return $fallback;
        }

        try {
            $raw = $this->aiProvider->complete(
                "Balas pelanggan toko {$store->name} singkat, ramah, Bahasa Indonesia (maks 400 karakter). Jangan JSON. Pertanyaan: {$message}"
            );
            $text = trim(preg_replace('/```.*?```/s', '', $raw) ?? $raw);

            return $text !== '' ? mb_substr($text, 0, 500) : $fallback;
        } catch (Throwable) {
            return $fallback;
        }
    }
}
