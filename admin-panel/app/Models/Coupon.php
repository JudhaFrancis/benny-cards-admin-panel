<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'type',
        'value',
        'status',
        'added_by',
        'modified_by'
    ];

    /**
     * Relationship: User who added the coupon.
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Relationship: User who last modified the coupon.
     */
    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
