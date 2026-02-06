<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'tracking_number',
        'order_date',
        'user_id',
        'items_count',
        'total_quantity',
        'net_amount',
        'coupons_id',
        'discount',
        'total_amount',
        'balance_due',
        'paid_amount',
        'payment_status',
        'status',
        'added_by',
        'modified_by'
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'net_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    // Relationships
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function customerDetails(): HasOne
    {
        return $this->hasOne(OrderCustomerDetails::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupons_id');
    }

    // Helper methods
    public function calculatePaidAmount(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function updatePaymentStatus(): void
    {
        $paid = $this->calculatePaidAmount();
        $total = $this->total_amount ?? 0;

        $this->paid_amount = (float) $paid;
        $this->balance_due = (float) max(0, $total - $paid);

        if ($paid <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($paid < $total) {
            $this->payment_status = 'due';
        } else {
            $this->payment_status = 'paid';
        }

        $this->save();
    }

    public function updatePaymentMethod(): void
    {
        // Note: payment_method column was removed from orders table
        // This is now handled via payments relationship
    }
}
