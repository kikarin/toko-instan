<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Services\SitemapService;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function __construct(protected SitemapService $sitemapService) {}

    public function sitemap(string $storeSlug): Response
    {
        $store = Store::query()->where('slug', $storeSlug)->firstOrFail();

        return response($this->sitemapService->xml($store), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function robots(string $storeSlug): Response
    {
        $store = Store::query()->where('slug', $storeSlug)->firstOrFail();

        return response($this->sitemapService->robots($store), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
