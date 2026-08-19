<?php

namespace App\Models;

use App\Models\Concerns\ScopedToTenant;
use App\Support\PublicMediaUrl;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory, ScopedToTenant;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'product_id',
        'user_id',
        'order_id',
        'order_item_id',
        'rating',
        'body',
        'photo_path',
    ];

    protected $appends = ['photo_url'];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function photoUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! filled($this->photo_path)) {
                return null;
            }

            return PublicMediaUrl::rewrite(Storage::disk('r2')->url($this->photo_path));
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
