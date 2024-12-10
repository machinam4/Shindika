<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = [
        "transaction_id",
        "player_code",
        "votes",
        "total_votes",
    ];
}
