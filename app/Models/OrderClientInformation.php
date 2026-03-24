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
        'job_details',
        'client_info',
        'card_specs',
        'added_by',
        'modified_by'
    ];

    protected $casts = [
        'job_details' => 'array',
        'client_info' => 'array',
        'card_specs' => 'array'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
