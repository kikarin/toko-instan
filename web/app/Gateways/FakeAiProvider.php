<?php

namespace App\Gateways;

use App\Contracts\AiProvider;

class FakeAiProvider implements AiProvider
{
    public function complete(string $prompt, array $options = []): string
    {
        preg_match('/Nama produk:\s*(.+)/u', $prompt, $nameMatch);
        preg_match('/Kategori:\s*(.+)/u', $prompt, $catMatch);
        preg_match('/Brand:\s*(.+)/u', $prompt, $brandMatch);

        $name = trim($nameMatch[1] ?? 'Produk');
        $category = trim($catMatch[1] ?? 'Umum');
        $brand = trim($brandMatch[1] ?? 'Toko');

        return json_encode([
            'description' => "{$name} dari {$brand} untuk kategori {$category}. Material berkualitas, nyaman dipakai sehari-hari, dan bergaransi keaslian. Cocok untuk pembeli yang mencari produk tahan lama dengan harga transparan.",
            'meta_title' => mb_substr("{$name} asli {$brand}", 0, 60),
            'meta_description' => mb_substr("Beli {$name} {$brand} original. Pengiriman aman, kualitas terjamin.", 0, 155),
            'tags' => array_values(array_filter([$category, $brand, 'original'])),
            'marketing_caption' => "✨ {$name} original {$brand} sudah ready!\nKualitas oke, packing rapi, ready kirim hari ini.\nKetik ORDER di DM / cek etalase toko 🛍️",
        ], JSON_UNESCAPED_UNICODE);
    }
}
