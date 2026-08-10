<?php

namespace App\Models;

use App\Models\Concerns\ScopedToTenant;
use Database\Factories\StoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property array<string, mixed>|null $showcase
 * @property array{primary?: string, secondary?: string, accent?: string, strong?: string}|null $theme_colors
 */
class Store extends Model
{
    /** @use HasFactory<StoreFactory> */
    use HasFactory, ScopedToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'category',
        'description',
        'logo',
        'status',
        'balance',
        'pending_escrow',
        'gmv',
        'total_orders',
        'rating',
        'badge',
        'avatar_hue',
        'banner_url',
        'banner_urls',
        'highlights',
        'hero_config',
        'phone',
        'email',
        'address',
        'instagram',
        'tiktok',
        'headline',
        'is_active',
        'npwp',
        'nik',
        'is_pkp',
        'tax_name',
        'tax_address',
        'theme',
        'theme_colors',
        'showcase',
    ];

    protected $casts = [
        'theme_colors' => 'array',
        'showcase' => 'array',
        'banner_urls' => 'array',
        'highlights' => 'array',
        'hero_config' => 'array',
        'is_active' => 'boolean',
        'is_pkp' => 'boolean',
    ];

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return HasOneThrough<Wallet, Tenant, $this>
     */
    public function wallet(): HasOneThrough
    {
        return $this->hasOneThrough(Wallet::class, Tenant::class, 'id', 'tenant_id', 'tenant_id', 'id');
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<Withdrawal, $this>
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }
}
