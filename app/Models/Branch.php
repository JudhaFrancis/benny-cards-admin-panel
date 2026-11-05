<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A shop/branch an order is placed in (NGL, MTM, Chennai, Online, ...).
 * Orders link to it through orders.branch_id.
 */
class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'is_active',
        'sort_order',
        'added_by',
        'modified_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(OrderEnquiry::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Resolve a branch filter value sent by the UI: a branch id, or (legacy) a branch name.
     * Returns null when nothing matches.
     */
    public static function resolveId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        return static::where('name', trim((string) $value))->value('id');
    }
}
