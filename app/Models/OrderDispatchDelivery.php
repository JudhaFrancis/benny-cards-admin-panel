<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDispatchDelivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_dispatch_delivery';

    protected $fillable = [
        'order_id',
        'status',
        'delivery_location',
        'dispatch_mode',
        'dispatch_details',
        'assigned_user_ids',
        'modified_by'
    ];

    protected $casts = [
        'delivery_location' => 'array',
        'dispatch_mode' => 'array',
        'dispatch_details' => 'array',
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
