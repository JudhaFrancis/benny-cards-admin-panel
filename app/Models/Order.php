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
        'order_date',
        'delivery_date',
        'user_id',
        'items_count',
        'total_quantity',
        'net_amount',
        'coupons_id',
        'discount',
        'extra_charges',
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
        'delivery_date' => 'datetime',
        'net_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'extra_charges' => 'decimal:2',
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


    // Individual Stage Tracking Relationships
    public function clientInformation(): HasOne
    {
        return $this->hasOne(OrderClientInformation::class);
    }

    public function designing(): HasOne
    {
        return $this->hasOne(OrderDesigning::class);
    }

    public function printing(): HasOne
    {
        return $this->hasOne(OrderPrinting::class);
    }

    public function packaging(): HasOne
    {
        return $this->hasOne(OrderPackaging::class);
    }

    public function dispatchDelivery(): HasOne
    {
        return $this->hasOne(OrderDispatchDelivery::class);
    }

    // Helper methods
    public function calculatePaidAmount(): float
    {
        return (float) $this->payments()->where('payment_status', 'completed')->sum('amount');
    }

    public function updatePaymentStatus(): void
    {
        $paid = $this->calculatePaidAmount();
        $total = $this->total_amount ?? 0;

        $this->paid_amount = $paid;
        $this->balance_due = max(0, $total - $paid);

        if ($paid <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($paid < $total) {
            $this->payment_status = 'due';
        } else {
            $this->payment_status = 'paid';
        }

        $this->save();
    }


    protected $appends = ['tracking_status_label'];

    public function getTrackingStatusLabelAttribute(): string
    {
        if ((float)$this->total_amount > 0 && (float)$this->total_amount === (float)$this->paid_amount) {
            return 'Completed';
        }

        // Higher stages priority
        $dispatch = $this->dispatchDelivery;
        if ($dispatch && $dispatch->status === 'Completed') return 'Dispatched';
        
        $packaging = $this->packaging;
        if ($packaging && $packaging->status === 'Completed') return 'Dispatched'; // Or 'Ready for Dispatch'
        if ($packaging && $packaging->status === 'Process') return 'Packaging Process';

        $printing = $this->printing;
        if ($printing && $printing->status === 'Completed') return 'Packaging Process';
        if ($printing && $printing->status === 'Process') return 'Printing Process';

        $designing = $this->designing;
        if ($designing && $designing->status === 'Completed') return 'Printing Process';
        if ($designing && $designing->status === 'Process') {
            $workAssign = $designing->work_assign ?? [];
            if (isset($workAssign['content_not_received']) && $workAssign['content_not_received']) return 'Content Not Received';
            return 'Designing Process';
        }

        $clientInfo = $this->clientInformation;
        if ($clientInfo && $clientInfo->status === 'Completed') return 'Confirmed';
        
        if ((float)$this->total_amount > (float)$this->paid_amount && (float)$this->paid_amount > 0) return 'Payment Pending';

        return 'New';
    }

    public function updatePaymentMethod(): void
    {
        // Handled via payments relationship
    }
}
