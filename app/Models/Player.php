<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $fillable = [
        "phone",
        "player_code",
        "invite_code",
        "transaction_id",
    ];

    public function votes()
    {
        return $this->hasMany(Vote::class, 'player_code', 'player_code');
    }

    public function totalvotes()
    {
        return $this->votes->count();
    }

    public static function generateAccountCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        } while (self::where('player_code', $code)->exists());

        return $code;
    }
}
