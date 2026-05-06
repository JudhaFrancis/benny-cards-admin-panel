<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPackaging extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_packaging';

    protected $fillable = [
        'order_id',
        'status',
        'packaging_logistics',
        'packaging_status',
        'assigned_user_ids',
        'modified_by'
    ];

    protected $casts = [
        'packaging_logistics' => 'array',
        'packaging_status' => 'array',
        'assigned_user_ids' => 'array'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }



    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
