<?php

namespace App\Services;

use App\Contracts\AiProvider;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreFaq;
use Throwable;

class StoreFaqService
{
    public function __construct(
        protected AiProvider $aiProvider,
        protected ActivityLogService $activityLog,
    ) {}

    /**
     * @return list<array{id: int, question: string, answer: string, source: string}>
     */
    public function listForStore(Store $store): array
    {
        return StoreFaq::query()
            ->where('store_id', $store->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (StoreFaq $f) => [
                'id' => $f->id,
                'question' => $f->question,
                'answer' => $f->answer,
                'source' => $f->source,
            ])
            ->all();
    }

    /**
     * @return list<array{id: int, question: string, answer: string, source: string}>
     */
    public function generateFromProducts(Store $store): array
    {
        $products = Product::query()
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->limit(8)
            ->get(['name', 'category', 'brand', 'price', 'description']);

        $lines = $products->map(fn (Product $p) => '- '.$p->name.' | '.$p->category.' | '.$p->brand.' | Rp '.(int) $p->price)->implode("\n");
        $prompt = "Buat 5 FAQ toko online Indonesia. Output JSON {\"faqs\":[{\"question\":\"...\",\"answer\":\"...\"}]}. Produk:\n".($lines ?: '- (belum ada produk)');

        $items = $this->parse($this->complete($prompt));
        StoreFaq::query()->where('store_id', $store->id)->where('source', 'ai')->delete();

        foreach ($items as $i => $item) {
            StoreFaq::query()->create([
                'tenant_id' => $store->tenant_id,
                'store_id' => $store->id,
                'question' => $item['question'],
                'answer' => $item['answer'],
                'sort_order' => $i,
                'source' => 'ai',
            ]);
        }

        $this->activityLog->record('faq_generated', Store::class, $store->id, ['count' => count($items)]);

        return $this->listForStore($store);
    }

    public function storeManual(Store $store, string $question, string $answer): StoreFaq
    {
        return StoreFaq::query()->create([
            'tenant_id' => $store->tenant_id,
            'store_id' => $store->id,
            'question' => $question,
            'answer' => $answer,
            'source' => 'manual',
            'sort_order' => 99,
        ]);
    }

    public function delete(Store $store, int $id): void
    {
        StoreFaq::query()->where('store_id', $store->id)->whereKey($id)->delete();
    }

    protected function complete(string $prompt): string
    {
        try {
            return $this->aiProvider->complete($prompt);
        } catch (Throwable) {
            return json_encode(['faqs' => $this->fallback()]);
        }
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    protected function parse(string $raw): array
    {
        $raw = trim($raw);
        if (preg_match('/```(?:json)?\s*(.*?)```/s', $raw, $m)) {
            $raw = trim($m[1]);
        }
        $decoded = json_decode($raw, true);
        $faqs = is_array($decoded) ? ($decoded['faqs'] ?? $decoded) : [];
        $out = [];
        foreach (is_array($faqs) ? $faqs : [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $q = trim((string) ($row['question'] ?? ''));
            $a = trim((string) ($row['answer'] ?? ''));
            if ($q !== '' && $a !== '') {
                $out[] = ['question' => $q, 'answer' => $a];
            }
        }

        return $out !== [] ? array_slice($out, 0, 8) : $this->fallback();
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    protected function fallback(): array
    {
        return [
            ['question' => 'Apakah produk original?', 'answer' => 'Ya, kami hanya menjual produk original dengan garansi toko.'],
            ['question' => 'Berapa lama pengiriman?', 'answer' => 'Pesanan diproses 1 hari kerja. Estimasi kurir tergantung kota tujuan.'],
            ['question' => 'Bagaimana cara retur?', 'answer' => 'Hubungi CS toko maksimal 2x24 jam setelah barang diterima, lengkap dengan foto.'],
        ];
    }
}
