<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileIncoming extends Model
{
    use HasFactory;

    protected $fillable = [
        'csp',
        'type',
        'shortcode',
        'api_pass',
        'api_user',
        'api_url',
        'api_key',
    ];
}
