<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEnquiryActivity extends Model
{
    protected $table = 'order_enquiry_activities';

    protected $fillable = [
        'enquiry_id',
        'type',
        'from_status',
        'to_status',
        'quoted_amount',
        'note',
        'created_by',
    ];

    protected $casts = [
        'quoted_amount' => 'decimal:2',
    ];

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(OrderEnquiry::class, 'enquiry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
