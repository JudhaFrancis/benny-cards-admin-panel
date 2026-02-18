<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_name',
        'template_content',
        'parameter',
        'allow_to_send',
        'status',
        'sender_id',
        'whatsapp_content',
        'module_id',
    ];
}