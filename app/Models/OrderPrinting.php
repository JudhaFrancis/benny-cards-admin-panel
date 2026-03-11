<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPrinting extends Model
{
    use HasFactory;

    protected $table = 'order_printing';

    protected $fillable = [
        'order_id',
        'status',
        'audit_details',
        'printing_status'
    ];

    protected $casts = [
        'audit_details' => 'array',
        'printing_status' => 'array'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
