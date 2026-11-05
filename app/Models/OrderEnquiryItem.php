<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEnquiryItem extends Model
{
    protected $table = 'order_enquiry_items';

    protected $fillable = [
        'enquiry_id',
        'product_id',
        'product_name',
        'product_image',
        'quantity',
        'unit_price',
        'discount_amount',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(OrderEnquiry::class, 'enquiry_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
