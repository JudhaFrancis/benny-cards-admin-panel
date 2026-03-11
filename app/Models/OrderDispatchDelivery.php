<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDispatchDelivery extends Model
{
    use HasFactory;

    protected $table = 'order_dispatch_delivery';

    protected $fillable = [
        'order_id',
        'status',
        'audit_details',
        'delivery_location',
        'dispatch_mode',
        'dispatch_details'
    ];

    protected $casts = [
        'audit_details' => 'array',
        'delivery_location' => 'array',
        'dispatch_mode' => 'array',
        'dispatch_details' => 'array'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
