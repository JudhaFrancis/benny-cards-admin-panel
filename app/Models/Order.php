<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

        protected $table = 'orders';

      

    // Order -> OrderItems relationship
    public function items()
    {
        return $this->hasMany(OrderItems::class, 'orders_id', 'id');
    }
    
}



