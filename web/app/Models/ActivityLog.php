<?php

namespace App\Models;

use Database\Factories\ActivityLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Immutable audit trail of important actions, stored in the `audit` schema
 * on PostgreSQL (schema-less `activity_logs` table on SQLite for tests).
 *
 * @property array<string, mixed>|null $properties
 */
class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $table = 'activity_logs';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'properties',
        'ip',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Enforce immutability: an existing entry can never be updated.
     *
     * @throws LogicException
     */
    public function save(array $options = []): bool
    {
        if ($this->exists) {
            throw new LogicException('Activity log entries are immutable.');
        }

        return parent::save($options);
    }

    /**
     * Enforce immutability: an entry can never be deleted.
     *
     * @throws LogicException
     */
    public function delete(): ?bool
    {
        throw new LogicException('Activity log entries are immutable.');
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
