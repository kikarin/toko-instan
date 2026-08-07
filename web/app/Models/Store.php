<?php

namespace App\Models;

use App\Models\Concerns\ScopedToTenant;
use Database\Factories\StoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

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
