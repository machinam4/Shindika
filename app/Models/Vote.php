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

    public function setVotesAttribute($value)
    {
        $this->attributes['votes'] = $value;

        // Add the votes to the total votes
        $this->attributes['total_votes'] = ($this->attributes['total_votes'] ?? 0) + $value;
    }
}
