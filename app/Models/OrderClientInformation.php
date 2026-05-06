<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderClientInformation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_client_information';

    protected $fillable = [
        'order_id',
        'status',
        'order_details',
        'client_info',
        'card_specs',
        'assigned_user_ids',
        'modified_by'
    ];

    protected $casts = [
        'order_details' => 'array',
        'client_info' => 'array',
        'card_specs' => 'array',
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
