<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Product;
use App\Models\Store;

class SeoService
{
    /**
     * @return array<string, mixed>
     */
    public function forStore(Store $store): array
    {
        $title = $store->name.' | Toko Online';
        $description = $this->clip($store->description ?: 'Belanja di '.$store->name.' — produk berkualitas, pengiriman aman.');
        $image = $store->logo ?: $store->banner_url;
        $url = url('/'.$store->slug);

        return $this->pack($title, $description, $image, $url);
    }

    /**
     * @return array<string, mixed>
     */
    public function forProduct(Store $store, Product $product): array
    {
        $title = ($product->meta_title ?: $product->name).' — '.$store->name;
        $raw = $product->meta_description ?: strip_tags((string) $product->description);
        $description = $this->clip($raw !== '' ? $raw : 'Beli '.$product->name.' di '.$store->name);
        $url = url('/'.$store->slug.'/p/'.$product->slug);

        $meta = $this->pack($title, $description, $product->img, $url, 'product');
        $meta['json_ld'] = $this->productJsonLd($store, $product, $url);

        return $meta;
    }

    /**
     * @return array<string, mixed>
     */
    public function forBlogIndex(Store $store): array
    {
        $title = 'Blog — '.$store->name;
        $description = $this->clip('Artikel dan tips dari '.$store->name);
        $url = url('/'.$store->slug.'/blog');

        return $this->pack($title, $description, $store->logo, $url);
    }

    /**
     * @return array<string, mixed>
     */
    public function forBlogPost(Store $store, BlogPost $post): array
    {
        $title = ($post->meta_title ?: $post->title).' — '.$store->name;
        $description = $this->clip($post->meta_description ?: $post->excerpt ?: strip_tags((string) $post->body));
        $url = url('/'.$store->slug.'/blog/'.$post->slug);

        return $this->pack($title, $description, $post->cover_url ?: $store->logo, $url, 'article');
    }

    /**
     * @return array<string, mixed>
     */
    public function productJsonLd(Store $store, Product $product, string $url): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $this->clip(strip_tags((string) $product->description) ?: $product->name, 300),
            'sku' => $product->sku,
            'image' => $product->img ? [$product->img] : [],
            'brand' => [
                '@type' => 'Brand',
                'name' => $product->brand ?: $store->name,
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => $url,
                'priceCurrency' => 'IDR',
                'price' => (string) (int) $product->price,
                'availability' => ((int) $product->stock > 0)
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $store->name,
                ],
            ],
        ];

        if ((float) $product->rating > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $product->rating,
                'bestRating' => '5',
                'worstRating' => '1',
            ];
        }

        return $schema;
    }

    /**
     * @return array{title: string, description: string, canonical: string, og_title: string, og_description: string, og_image: ?string, og_url: string, og_type: string}
     */
    protected function pack(string $title, string $description, ?string $image, string $url, string $ogType = 'website'): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $url,
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $image,
            'og_url' => $url,
            'og_type' => $ogType,
        ];
    }

    protected function clip(?string $text, int $max = 160): string
    {
        $text = trim(preg_replace('/\s+/', ' ', (string) $text) ?? '');

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $max - 1)).'…';
    }
}
