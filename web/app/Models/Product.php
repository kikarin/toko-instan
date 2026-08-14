<?php

namespace App\Models;

use App\Enums\ProductType;
use App\Support\PublicMediaUrl;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'slug',
        'category',
        'price',
        'sold',
        'rating',
        'tag',
        'img',
        'stock',
        'is_active',
        'description',
        'type',
        'digital_file_path',
        'digital_file_name',
        'digital_file_mime',
        'sku',
        'brand',
        'weight_gram',
        'variant_options',
    ];

    protected $casts = [
        'variant_options' => 'array',
    ];

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function img(): Attribute
    {
        return Attribute::get(fn (?string $value): ?string => PublicMediaUrl::rewrite($value));
    }

    public function isDigital(): bool
    {
        return $this->type === ProductType::Digital->value;
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
