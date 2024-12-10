<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposits extends Model
{
    use HasFactory;

    protected $fillable = [
        'ResultCode',
        'MerchantRequestID',
        'CheckoutRequestID',
        'TransactionType',
        'TransID',
        'TransTime',
        'TransAmount',
        'BusinessShortCode',
        'BillRefNumber',
        'InvoiceNumber',
        'OrgAccountBalance',
        'ThirdPartyTransID',
        'MSISDN',
        'FirstName',
        'MiddleName',
        'LastName',
        'SmsShortcode',
    ];

    public function player()
    {
        return $this->hasOne(Player::class, 'BillRefNumber', 'player_code');
    }

    public function paybill()
    {
        return $this->hasOne(PaybillWallet::class, 'shortcode', 'BusinessShortCode');
    }

    // public function platform()
    // {
    //     return $this->hasOne(Platforms::class, 'id', 'SmsShortcode');
    // }
}
