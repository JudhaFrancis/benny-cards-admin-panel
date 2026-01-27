<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

        protected $table = 'orders';

        protected $fillable = [
        'order_number',
        'tracking_id',
        'order_date',
        'user_id',
        'items_count',
        'total_quantity',
        'net_amount',
        'coupons_id',
        'discount_amount',
        'payment_status',
        // add more columns as needed
    ];

      

    // Order -> OrderItems relationship
    public function items()
    {
        return $this->hasMany(OrderItems::class, 'orders_id', 'id');
    }
    
}



