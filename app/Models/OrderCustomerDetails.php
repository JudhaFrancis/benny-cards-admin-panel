<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCustomerDetails extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_customer_details';

    protected $fillable = [
        'order_id',
        'name',
        'email',
        'phone',
        'country',
        'address_1',
        'city_1',
        'state_1',
        'post_code_1',
        'address_2',
        'city_2',
        'state_2',
        'post_code_2',
        'remarks',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
