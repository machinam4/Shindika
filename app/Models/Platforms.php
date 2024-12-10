<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Platforms extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'mobile_incoming',
        'mobile_outgoing',
        'b2c_wallet',
        'paybill_wallet',
        'wallet_price',
        'vote_price',
        'win_ratio',
        'win_maximum',
        'win_minimum',
    ];

    public function incoming()
    {
        return $this->hasOne(MobileIncoming::class, 'id', 'mobile_incoming');
    }

    public function outgoing()
    {
        return $this->hasOne(MobileOutgoing::class, 'id', 'mobile_outgoing');
    }

    public function paybill()
    {
        return $this->hasOne(PaybillWallet::class, 'id', 'paybill_wallet');
    }

    public function b2c()
    {
        return $this->hasOne(B2CWallet::class, 'id', 'b2c_wallet');
    }

    public function setActiveAttribute($value)
    {
        if ($value) {
            // Deactivate all other records
            static::where('active', true)->update(['active' => false]);
        }
        $this->attributes['active'] = $value;
    }
}
