<?php

namespace App\Gateways;

use App\Contracts\AiProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiProvider implements AiProvider
{
    public function complete(string $prompt, array $options = []): string
    {
        $key = (string) config('ai.openai.key');
        if ($key === '') {
            throw new RuntimeException('OPENAI_API_KEY belum dikonfigurasi.');
        }

        $response = Http::withToken($key)
            ->acceptJson()
            ->timeout(45)
            ->post(rtrim((string) config('ai.openai.base_url'), '/').'/chat/completions', [
                'model' => config('ai.openai.model'),
                'temperature' => $options['temperature'] ?? 0.6,
                'max_tokens' => $options['max_tokens'] ?? 700,
                'messages' => [
                    ['role' => 'system', 'content' => 'Kamu copywriter e-commerce Indonesia. Jawab HANYA JSON valid, tanpa markdown.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('OpenAI gagal: '.$response->status());
        }

        $text = (string) data_get($response->json(), 'choices.0.message.content');
        if ($text === '') {
            throw new RuntimeException('OpenAI tidak mengembalikan teks.');
        }

        return $text;
    }
}
