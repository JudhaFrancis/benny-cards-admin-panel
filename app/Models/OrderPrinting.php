<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPrinting extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_printing';

    protected $fillable = [
        'order_id',
        'status',
        'printing_status',
        'assigned_user_ids',
        'modified_by'
    ];

    protected $casts = [
        'printing_status' => 'array',
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
