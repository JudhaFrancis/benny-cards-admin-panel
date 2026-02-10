<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'job_details',
        'client_info',
        'card_specs',
        'work_assign',
        'design_print',
        'printing_status',
        'packaging_logistics',
        'packaging_status',
        'delivery_location',
        'dispatch_mode',
        'dispatch_details',
        'payment_info',
    ];

    protected $casts = [
        'job_details' => 'array',
        'client_info' => 'array',
        'card_specs' => 'array',
        'work_assign' => 'array',
        'design_print' => 'array',
        'printing_status' => 'array',
        'packaging_logistics' => 'array',
        'packaging_status' => 'array',
        'delivery_location' => 'array',
        'dispatch_mode' => 'array',
        'dispatch_details' => 'array',
        'payment_info' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
