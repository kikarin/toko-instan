<?php

namespace App\Models;

use App\Models\Concerns\ScopedToTenant;
use App\Support\PublicMediaUrl;
use Database\Factories\BlogPostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class BlogPost extends Model
{
    /** @use HasFactory<BlogPostFactory> */
    use HasFactory, ScopedToTenant;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'user_id',
        'blog_category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_path',
        'meta_title',
        'meta_description',
        'is_published',
        'published_at',
    ];

    protected $appends = ['cover_url'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! filled($this->cover_path)) {
                return null;
            }

            if (str_starts_with($this->cover_path, 'http')) {
                return PublicMediaUrl::rewrite($this->cover_path);
            }

            return PublicMediaUrl::rewrite(Storage::disk('r2')->url($this->cover_path));
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }
}
