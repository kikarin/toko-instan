<?php

namespace App\Services;

use App\DTO\BlogPostData;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

class BlogService
{
    /**
     * @return list<BlogPost>
     */
    public function listForStore(Store $store, bool $publishedOnly = false): array
    {
        return BlogPost::query()
            ->with(['category', 'author:id,name', 'tags'])
            ->where('store_id', $store->id)
            ->when($publishedOnly, fn ($q) => $q->published())
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get()
            ->all();
    }

    public function findPublished(Store $store, string $slug): ?BlogPost
    {
        return BlogPost::query()
            ->with(['category', 'author:id,name', 'tags'])
            ->where('store_id', $store->id)
            ->where('slug', $slug)
            ->published()
            ->first();
    }

    public function create(Store $store, User $author, BlogPostData $dto): BlogPost
    {
        $this->assertUniqueSlug($store, $dto->slug);

        $post = BlogPost::query()->create([
            ...$this->payload($store, $dto),
            'user_id' => $author->id,
            'published_at' => $dto->isPublished ? now() : null,
        ]);

        $this->syncTags($store, $post, $dto->tagNames);

        return $post->load(['category', 'tags', 'author']);
    }

    public function update(BlogPost $post, BlogPostData $dto): BlogPost
    {
        $this->assertUniqueSlug($post->store, $dto->slug, $post->id);
        $data = $this->payload($post->store, $dto);
        $data['published_at'] = $dto->isPublished
            ? ($post->published_at ?? now())
            : null;
        $post->update($data);
        $this->syncTags($post->store, $post, $dto->tagNames);

        return $post->refresh()->load(['category', 'tags', 'author']);
    }

    public function delete(BlogPost $post): void
    {
        $post->delete();
    }

    public function createCategory(Store $store, string $name): BlogCategory
    {
        $slug = Str::slug($name) ?: 'kategori';

        return BlogCategory::query()->firstOrCreate(
            ['store_id' => $store->id, 'slug' => $slug],
            ['tenant_id' => $store->tenant_id, 'name' => $name],
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(Store $store, BlogPostData $dto): array
    {
        return [
            'tenant_id' => $store->tenant_id,
            'store_id' => $store->id,
            'blog_category_id' => $dto->categoryId,
            'title' => $dto->title,
            'slug' => $dto->slug,
            'excerpt' => $dto->excerpt,
            'body' => $dto->body,
            'cover_path' => $dto->coverPath,
            'meta_title' => $dto->metaTitle,
            'meta_description' => $dto->metaDescription,
            'is_published' => $dto->isPublished,
        ];
    }

    /**
     * @param  list<string>  $names
     */
    protected function syncTags(Store $store, BlogPost $post, array $names): void
    {
        $ids = [];
        foreach ($names as $name) {
            $tag = BlogTag::query()->firstOrCreate(
                ['store_id' => $store->id, 'slug' => Str::slug($name) ?: Str::random(6)],
                ['tenant_id' => $store->tenant_id, 'name' => $name],
            );
            $ids[] = $tag->id;
        }
        $post->tags()->sync($ids);
    }

    protected function assertUniqueSlug(Store $store, string $slug, ?int $ignoreId = null): void
    {
        $exists = BlogPost::query()
            ->where('store_id', $store->id)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            throw new RuntimeException('Slug artikel sudah dipakai.');
        }
    }
}
