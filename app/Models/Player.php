<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $fillable = [
        'phone',
        'player_code',
        'invite_code',
        'transaction_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Automatically set the player_code before creating a new player
            $model->player_code = self::generateAccountCode();
        });
    }

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
        $lastCode = self::orderBy('id', 'desc')->value('player_code');
        $nextCode = $lastCode ? strtoupper(self::incrementCode($lastCode)) : '0000';

        return str_pad($nextCode, 4, '0', STR_PAD_LEFT);
    }

    public static function incrementCode($code)
    {
        $base = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codeValue = base_convert($code, 36, 10);
        $codeValue++;

        return strtoupper(base_convert($codeValue, 10, 36));
    }
}
