<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDesigning extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_designing';

    protected $fillable = [
        'order_id',
        'status',
        'work_assign',
        'design_print',
        'modified_by'
    ];

    protected $casts = [
        'work_assign' => 'array',
        'design_print' => 'array'
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
