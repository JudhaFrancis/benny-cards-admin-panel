<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'signature_name',
        'payment_number',
        'payment_date',
        'payment_status',
        'payment_details',
        'notes',
        'added_by',
        'modified_by',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'payment_details' => 'array',
    ];

    protected $appends = ['amount'];

    public function getAmountAttribute()
    {
        if (!is_array($this->payment_details)) return 0;
        return array_reduce($this->payment_details, function ($carry, $item) {
            return $carry + (float)($item['amount'] ?? 0);
        }, 0);
    }

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
