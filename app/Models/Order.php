<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'customer_id',
        'order_number',
        'status',
        'total_amount',
        'payment_status',
        'shipping_address',
        'billing_address',
        'placed_at',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'placed_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        // Assuming users table handles customers or there is a specific customers table
        // Adjusting to 'User' model (standard Laravel) or 'Customer' if it exists.
        // User requested Example Models "Order, OrderItem, Product". 
        // I will assume a generic "Customer" model is used for the relationship but won't implement the full module.
        return $this->belongsTo(Customer::class);
    }
}
