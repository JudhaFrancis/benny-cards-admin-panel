<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPackaging extends Model
{
    use HasFactory;

    protected $table = 'order_packaging';

    protected $fillable = [
        'order_id',
        'status',
        'audit_details',
        'packaging_logistics',
        'packaging_status'
    ];

    protected $casts = [
        'audit_details' => 'array',
        'packaging_logistics' => 'array',
        'packaging_status' => 'array'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
