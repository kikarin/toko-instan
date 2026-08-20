<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Product;
use App\Models\Store;

class SitemapService
{
    public function xml(Store $store): string
    {
        $urls = [];
        $base = rtrim(url('/'.$store->slug), '/');

        $urls[] = $this->url($base, $store->updated_at, 'daily', '1.0');
        $urls[] = $this->url($base.'/blog', now(), 'weekly', '0.6');

        Product::query()
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at'])
            ->each(function (Product $p) use (&$urls, $base) {
                $urls[] = $this->url($base.'/p/'.$p->slug, $p->updated_at, 'weekly', '0.8');
            });

        BlogPost::query()
            ->where('store_id', $store->id)
            ->published()
            ->orderByDesc('published_at')
            ->get(['slug', 'updated_at'])
            ->each(function (BlogPost $p) use (&$urls, $base) {
                $urls[] = $this->url($base.'/blog/'.$p->slug, $p->updated_at, 'weekly', '0.5');
            });

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .implode('', $urls)
            .'</urlset>';
    }

    public function robots(Store $store): string
    {
        $sitemap = url('/'.$store->slug.'/sitemap.xml');

        return "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /dashboard\nSitemap: {$sitemap}\n";
    }

    protected function url(string $loc, mixed $lastmod, string $freq, string $priority): string
    {
        $mod = $lastmod instanceof \DateTimeInterface ? $lastmod->format('Y-m-d') : now()->format('Y-m-d');

        return '<url><loc>'.htmlspecialchars($loc, ENT_XML1).'</loc><lastmod>'.$mod.'</lastmod>'
            .'<changefreq>'.$freq.'</changefreq><priority>'.$priority.'</priority></url>';
    }
}
