<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderClientInformation extends Model
{
    use HasFactory;

    protected $table = 'order_client_information';

    protected $fillable = [
        'order_id',
        'status',
        'audit_details',
        'job_details',
        'client_info',
        'card_specs'
    ];

    protected $casts = [
        'audit_details' => 'array',
        'job_details' => 'array',
        'client_info' => 'array',
        'card_specs' => 'array'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
