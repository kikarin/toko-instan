<?php

namespace App\Models;

use App\Models\Concerns\ScopedToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreFaq extends Model
{
    use ScopedToTenant;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'product_id',
        'question',
        'answer',
        'sort_order',
        'source',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
