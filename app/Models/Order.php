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
        'priority',
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
        $payments = $this->payments()->where('payment_status', 'completed')->get();
        return (float) $payments->sum(function ($payment) {
            return (float) $payment->amount; // Uses the getAmountAttribute accessor
        });
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


    protected $appends = [
        'resolved_status',
        'design_finalized_date',
        'order_taken_by',
        'order_placed_in',
        'assigned_name'
    ];

    public function getDesignFinalizedDateAttribute(): ?string
    {
        return $this->designing?->work_assign['completed_date'] ?? null;
    }

    public function getOrderTakenByAttribute(): ?string
    {
        return $this->clientInformation?->order_details['order_taken_by'] ?? null;
    }

    public function getOrderPlacedInAttribute(): ?string
    {
        return $this->clientInformation?->order_details['order_placed_in'] ?? null;
    }

    public function getAssignedNameAttribute(): ?string
    {
        $status = $this->resolved_status;
        
        if ($status === 'Designing in Progress') {
            return $this->designing?->work_assign['assigned_to'] ?? null;
        }
        if ($status === 'Printing in Progress') {
            return $this->printing?->printing_status['assigned_to'] 
                ?? $this->designing?->work_assign['assigned_to'] 
                ?? null;
        }
        if ($status === 'Packing in Progress') {
            $log = $this->packaging?->packaging_logistics;
            if (!$log) return null;
            $names = [];
            if (is_array($log['assigned_by_multiple'] ?? null)) $names = array_merge($names, $log['assigned_by_multiple']);
            if (is_array($log['crafted_by_multiple'] ?? null)) $names = array_merge($names, $log['crafted_by_multiple']);
            if (!empty($log['crafted_by']) && !in_array($log['crafted_by'], $names)) $names[] = $log['crafted_by'];
            return !empty($names) ? implode(", ", array_unique($names)) : null;
        }
        if ($status === 'Out for Delivery') {
            return $this->dispatchDelivery?->dispatch_mode['packed_by'] ?? null;
        }

        return null;
    }

    public function getResolvedStatusAttribute(): string
    {
        $dispatch = $this->dispatchDelivery;
        if ($dispatch) {
            if ($dispatch->status === 'Completed') return 'Delivered';
            if ($dispatch->status === 'Process') return 'Out for Delivery';
        }

        $packaging = $this->packaging;
        if ($packaging) {
            if ($packaging->status === 'Completed') return 'Packed';
            if ($packaging->status === 'Process') return 'Packing in Progress';
        }

        $printing = $this->printing;
        if ($printing) {
            if ($printing->status === 'Completed') return 'Printed';
            if ($printing->status === 'Process') return 'Printing in Progress';
        }

        $designing = $this->designing;
        if ($designing) {
            if ($designing->status === 'Completed') return 'Designed';
            if ($designing->status === 'Process') return 'Designing in Progress';
        }

        $clientInfo = $this->clientInformation;
        if ($clientInfo) {
            if ($clientInfo->status === 'Completed') return 'Confirmed';
        }

        return 'New Order';
    }

    public function updatePaymentMethod(): void
    {
        // Handled via payments relationship
    }
}
