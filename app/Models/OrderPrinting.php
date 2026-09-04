<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPrinting extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_printing';

    protected $fillable = [
        'order_id',
        'status',
        'printing_status',
        'assigned_user_ids',
        'modified_by'
    ];

    protected $casts = [
        'assigned_user_ids' => 'array'
    ];

    /**
     * Handle backward compatibility for company_name -> company_names array.
     */
    protected function printingStatus(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                if (!$value) return [];
                
                // If it's a string from the database, decode it
                $data = is_string($value) ? json_decode($value, true) : $value;
                if (!is_array($data)) $data = [];

                if (isset($data['company_name']) && !isset($data['company_names'])) {
                    $data['company_names'] = [$data['company_name']];
                    unset($data['company_name']);
                }

                return $data;
            },
            set: function ($value) {
                return json_encode($value);
            }
        );
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }



    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
