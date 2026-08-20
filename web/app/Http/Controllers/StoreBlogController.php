<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Services\BlogService;
use App\Services\SeoService;
use Inertia\Inertia;
use Inertia\Response;

class StoreBlogController extends Controller
{
    public function __construct(
        protected BlogService $blogService,
        protected SeoService $seoService,
    ) {}

    public function index(string $storeSlug): Response
    {
        $store = Store::query()->where('slug', $storeSlug)->firstOrFail();
        $posts = collect($this->blogService->listForStore($store, publishedOnly: true))
            ->map(fn ($p) => [
                'title' => $p->title,
                'slug' => $p->slug,
                'excerpt' => $p->excerpt,
                'cover_url' => $p->cover_url,
                'category' => $p->category?->name,
                'author' => $p->author?->name,
                'published_at' => $p->published_at?->format('d M Y'),
            ]);

        return Inertia::render('Blog/StoreIndex', [
            'store' => ['name' => $store->name, 'slug' => $store->slug],
            'posts' => $posts,
            'seo' => $this->seoService->forBlogIndex($store),
        ]);
    }

    public function show(string $storeSlug, string $postSlug): Response
    {
        $store = Store::query()->where('slug', $storeSlug)->firstOrFail();
        $post = $this->blogService->findPublished($store, $postSlug)
            ?? abort(404);

        return Inertia::render('Blog/Show', [
            'store' => ['name' => $store->name, 'slug' => $store->slug],
            'post' => [
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'body' => $post->body,
                'cover_url' => $post->cover_url,
                'category' => $post->category?->name,
                'author' => $post->author?->name,
                'tags' => $post->tags->pluck('name')->all(),
                'published_at' => $post->published_at?->format('d M Y'),
            ],
            'seo' => $this->seoService->forBlogPost($store, $post),
        ]);
    }
}
