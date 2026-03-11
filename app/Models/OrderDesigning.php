<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDesigning extends Model
{
    use HasFactory;

    protected $table = 'order_designing';

    protected $fillable = [
        'order_id',
        'status',
        'audit_details',
        'work_assign',
        'design_print'
    ];

    protected $casts = [
        'audit_details' => 'array',
        'work_assign' => 'array',
        'design_print' => 'array'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
