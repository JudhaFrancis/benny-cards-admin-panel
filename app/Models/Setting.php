<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'description',
        'short_des',
        'logo',
        'photo',
        'address',
        'phone',
        'email',
        'app_version',
        'facebook_url',
        'instagram_url',
        'branches',
    ];

    protected $casts = [
        'branches' => 'array',
    ];
}
