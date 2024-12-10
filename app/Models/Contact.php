<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        "phone",
        "step"
    ];

    public function player()
    {
        return $this->hasOne(Player::class, 'phone', 'phone');
    }
}
