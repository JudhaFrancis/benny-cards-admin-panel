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

        $this->syncTrackingWithPayments();
    }

    /**
     * Sync order tracking payment_info with the latest payment details.
     */
    public function syncTrackingWithPayments(): void
    {
        $payments = $this->payments()
            ->where('payment_status', 'completed')
            ->where('amount', '>', 0)
            ->get();

        if ($payments->count() > 0) {
            $paymentInfoArray = $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_method' => $payment->payment_method,
                    'transaction_id' => $payment->transaction_id,
                    'payment_date' => $payment->payment_date ? $payment->payment_date->toDateString() : null,
                    'amount' => (float) $payment->amount,
                    'signature_name' => $payment->signature_name,
                    '_audit' => [
                        'updated_by' => $payment->addedBy?->name ?? 'System',
                        'updated_at' => $payment->updated_at->toDateTimeString(),
                    ]
                ];
            })->toArray();

            $this->tracking()->updateOrCreate(
                ['order_id' => $this->id],
                [
                    'payment_info' => [
                        'payments' => $paymentInfoArray,
                        '_audit' => [
                            'updated_by' => 'System',
                            'updated_at' => now()->toDateTimeString(),
                        ]
                    ]
                ]
            );
        } else {
            // Clear payment info if no completed payments exist
            if ($this->tracking) {
                $this->tracking->update([
                    'payment_info' => [
                        'payments' => [],
                        '_audit' => [
                            'updated_by' => 'System',
                            'updated_at' => now()->toDateTimeString(),
                        ]
                    ]
                ]);
            }
        }
    }

    protected $appends = ['tracking_status_label'];

    public function getTrackingStatusLabelAttribute(): string
    {
        $tracking = $this->tracking;
        if (!$tracking) {
            return 'New';
        }

        // 6. Paid/Completed
        if (isset($tracking->payment_info['_audit'])) {
            return (float) $this->total_amount == (float) $this->paid_amount ? 'Completed' : 'Pending';
        }

        // 5. Dispatched
        if (
            isset($tracking->dispatch_details['_audit']) ||
            isset($tracking->dispatch_mode['_audit']) ||
            isset($tracking->delivery_location['_audit'])
        ) {
            return 'Dispatched';
        }

        // 4. Packed
        if (
            isset($tracking->packaging_status['_audit']) ||
            isset($tracking->packaging_logistics['_audit'])
        ) {
            return 'Packed';
        }

        // 3. Processing
        if (
            isset($tracking->printing_status['_audit']) ||
            isset($tracking->design_print['_audit'])
        ) {
            return 'Processing';
        }

        // 2. Assigned
        if (isset($tracking->work_assign['_audit'])) {
            return 'Assigned';
        }

        // 1. Confirmed
        if (
            isset($tracking->card_specs['_audit']) ||
            isset($tracking->client_info['_audit']) ||
            isset($tracking->job_details['_audit'])
        ) {
            return 'Confirmed';
        }

        return 'New';
    }

    public function tracking(): HasOne
    {
        return $this->hasOne(OrderTracking::class);
    }

    public function updatePaymentMethod(): void
    {
        // Note: payment_method column was removed from orders table
        // This is now handled via payments relationship
    }
}
