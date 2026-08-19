<?php

namespace App\Gateways;

use App\Contracts\AiProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProvider implements AiProvider
{
    public function complete(string $prompt, array $options = []): string
    {
        $key = (string) config('ai.gemini.key');
        if ($key === '') {
            throw new RuntimeException('GEMINI_API_KEY belum dikonfigurasi.');
        }

        $model = (string) config('ai.gemini.model', 'gemini-2.0-flash');
        $url = rtrim((string) config('ai.gemini.base_url'), '/').'/models/'.$model.':generateContent';

        $response = Http::withQueryParameters(['key' => $key])
            ->acceptJson()
            ->timeout(45)
            ->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'temperature' => $options['temperature'] ?? 0.6,
                    'maxOutputTokens' => $options['max_tokens'] ?? 700,
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Gemini gagal: '.$response->status());
        }

        $text = (string) data_get($response->json(), 'candidates.0.content.parts.0.text');
        if ($text === '') {
            throw new RuntimeException('Gemini tidak mengembalikan teks.');
        }

        return $text;
    }
}
