<?php

namespace App\Models;

use App\Models\Concerns\ScopedToTenant;
use Database\Factories\BlogCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogCategory extends Model
{
    /** @use HasFactory<BlogCategoryFactory> */
    use HasFactory, ScopedToTenant;

    protected $fillable = ['tenant_id', 'store_id', 'name', 'slug'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }
}
