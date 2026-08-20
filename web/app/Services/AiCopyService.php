<?php

namespace App\Services;

use App\Contracts\AiProvider;
use RuntimeException;

class AiCopyService
{
    public function __construct(protected AiProvider $provider) {}

    /**
     * @param  array{name: string, category?: ?string, brand?: ?string, price?: int|string|null, description?: ?string, tasks?: list<string>}  $input
     * @return array{description: ?string, meta_title: ?string, meta_description: ?string, tags: list<string>, marketing_caption: ?string}
     */
    public function generateProductCopy(array $input): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            throw new RuntimeException('Nama produk wajib diisi sebelum generate AI.');
        }

        $tasks = $input['tasks'] ?? ['description', 'seo', 'caption'];
        $prompt = $this->prompt($input, $tasks);
        $raw = $this->provider->complete($prompt);

        return $this->normalize($this->decode($raw), $tasks);
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  list<string>  $tasks
     */
    protected function prompt(array $input, array $tasks): string
    {
        $bits = [
            'Tulis copy toko online Indonesia. Output JSON dengan kunci: description, meta_title, meta_description, tags (array string), marketing_caption.',
            'Nama produk: '.$input['name'],
            'Kategori: '.($input['category'] ?? '-'),
            'Brand: '.($input['brand'] ?? '-'),
            'Harga: '.($input['price'] ?? '-'),
            'Deskripsi existing: '.($input['description'] ?? '-'),
            'Tugas: '.implode(', ', $tasks),
            'meta_title max 60 karakter. meta_description max 155. description 2-4 kalimat. caption Instagram/WhatsApp, pakai newline.',
        ];

        return implode("\n", $bits);
    }

    /**
     * @return array<string, mixed>
     */
    protected function decode(string $raw): array
    {
        $raw = trim($raw);
        if (preg_match('/```(?:json)?\s*(.*?)```/s', $raw, $m)) {
            $raw = trim($m[1]);
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return ['description' => $raw];
        }

        return $decoded;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $tasks
     * @return array{description: ?string, meta_title: ?string, meta_description: ?string, tags: list<string>, marketing_caption: ?string}
     */
    protected function normalize(array $data, array $tasks): array
    {
        $tags = $data['tags'] ?? [];
        if (is_string($tags)) {
            $tags = preg_split('/\s*,\s*/', $tags) ?: [];
        }

        $out = [
            'description' => isset($data['description']) ? trim((string) $data['description']) : null,
            'meta_title' => isset($data['meta_title']) ? mb_substr(trim((string) $data['meta_title']), 0, 70) : null,
            'meta_description' => isset($data['meta_description']) ? mb_substr(trim((string) $data['meta_description']), 0, 160) : null,
            'tags' => array_values(array_filter(array_map(fn ($t) => trim((string) $t), $tags))),
            'marketing_caption' => isset($data['marketing_caption']) ? trim((string) $data['marketing_caption']) : null,
        ];

        if (! in_array('description', $tasks, true)) {
            $out['description'] = null;
        }
        if (! in_array('seo', $tasks, true)) {
            $out['meta_title'] = null;
            $out['meta_description'] = null;
            $out['tags'] = [];
        }
        if (! in_array('caption', $tasks, true)) {
            $out['marketing_caption'] = null;
        }

        return $out;
    }
}
