<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'message_type',
        'status',
        'resend',
        'created_by',
        'sender_mobile_no',
        'recipient_mobile_no',
        'response',
    ];
    
}