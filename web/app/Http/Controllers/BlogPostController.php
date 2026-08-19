<?php

namespace App\Http\Controllers;

use App\DTO\BlogPostData;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\BlogService;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class BlogPostController extends Controller
{
    public function __construct(
        protected BlogService $blogService,
        protected StoreService $storeService,
    ) {}

    public function index(Request $request): Response
    {
        $store = $this->storeService->getActiveStore($request->user()->id);

        $posts = $store
            ? collect($this->blogService->listForStore($store))->map(fn (BlogPost $p) => $this->format($p))
            : collect();

        $categories = $store
            ? BlogCategory::query()->where('store_id', $store->id)->orderBy('name')->get(['id', 'name'])
            : collect();

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $store = $this->storeService->getActiveStore($request->user()->id)
            ?? throw ValidationException::withMessages(['title' => 'Toko tidak ditemukan.']);

        try {
            $this->blogService->create($store, $request->user(), BlogPostData::fromRequest($request));
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['slug' => $e->getMessage()]);
        }

        return back()->with('success', 'Artikel disimpan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        try {
            $this->blogService->update($this->owned($request, $id), BlogPostData::fromRequest($request));
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['slug' => $e->getMessage()]);
        }

        return back()->with('success', 'Artikel diperbarui.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $this->blogService->delete($this->owned($request, $id));

        return back()->with('success', 'Artikel dihapus.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $store = $this->storeService->getActiveStore($request->user()->id)
            ?? throw ValidationException::withMessages(['name' => 'Toko tidak ditemukan.']);

        $validated = $request->validate(['name' => ['required', 'string', 'max:80']]);
        $this->blogService->createCategory($store, $validated['name']);

        return back()->with('success', 'Kategori blog ditambah.');
    }

    protected function owned(Request $request, int $id): BlogPost
    {
        $store = $this->storeService->getActiveStore($request->user()->id);

        return BlogPost::query()->where('store_id', $store?->id)->findOrFail($id);
    }

    /**
     * @return array<string, mixed>
     */
    protected function format(BlogPost $p): array
    {
        return [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'excerpt' => $p->excerpt,
            'body' => $p->body,
            'cover_path' => $p->cover_path,
            'cover_url' => $p->cover_url,
            'meta_title' => $p->meta_title,
            'meta_description' => $p->meta_description,
            'blog_category_id' => $p->blog_category_id,
            'category' => $p->category?->name,
            'author' => $p->author?->name,
            'tags' => $p->tags->pluck('name')->implode(', '),
            'is_published' => $p->is_published,
            'published_at' => $p->published_at?->format('d M Y'),
        ];
    }
}
