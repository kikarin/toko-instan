<?php

namespace App\DTO;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BlogPostData
{
    /**
     * @param  list<string>  $tagNames
     */
    public function __construct(
        public string $title,
        public string $slug,
        public ?string $excerpt,
        public ?string $body,
        public ?int $categoryId,
        public array $tagNames,
        public ?string $coverPath,
        public ?string $metaTitle,
        public ?string $metaDescription,
        public bool $isPublished,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:160'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'blog_category_id' => ['nullable', 'integer'],
            'tags' => ['nullable', 'string', 'max:255'],
            'cover_path' => ['nullable', 'string', 'max:500'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'is_published' => ['nullable', 'boolean'],
        ])->validate();

        $title = $validated['title'];
        $slug = Str::slug($validated['slug'] ?? $title) ?: Str::slug($title);
        $tags = collect(explode(',', (string) ($validated['tags'] ?? '')))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return new self(
            title: $title,
            slug: $slug,
            excerpt: $validated['excerpt'] ?? null,
            body: $validated['body'] ?? null,
            categoryId: isset($validated['blog_category_id']) ? (int) $validated['blog_category_id'] : null,
            tagNames: $tags,
            coverPath: $validated['cover_path'] ?? null,
            metaTitle: $validated['meta_title'] ?? null,
            metaDescription: $validated['meta_description'] ?? null,
            isPublished: (bool) ($validated['is_published'] ?? false),
        );
    }
}
