<?php

namespace App\Models;

use App\Models\Concerns\ScopedToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreChat extends Model
{
    use ScopedToTenant;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'session_key',
        'visitor_name',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(StoreChatMessage::class);
    }
}
