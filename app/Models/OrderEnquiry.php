<?php

namespace App\Models;

use App\Enums\EnquiryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEnquiry extends Model
{
    use SoftDeletes;

    protected $table = 'order_enquiries';

    protected $fillable = [
        'enquiry_number',
        'source',
        'reference',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'occasion',
        'order_placed_in',
        'card_type',
        'card_size',
        'specifications',
        'budget_tier',
        'expected_delivery_date',
        'message',
        'attachments',
        'status',
        'priority',
        'assigned_user_ids',
        'assigned_to',
        'next_follow_up_at',
        'quoted_amount',
        'lost_reason',
        'converted_order_id',
        'converted_at',
        'added_by',
        'modified_by',
    ];

    protected $casts = [
        'attachments' => 'array',
        'assigned_user_ids' => 'array',
        'expected_delivery_date' => 'date',
        'next_follow_up_at' => 'datetime',
        'converted_at' => 'datetime',
        'quoted_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderEnquiryItem::class, 'enquiry_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(OrderEnquiryActivity::class, 'enquiry_id')->orderBy('created_at', 'desc');
    }

    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
