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
        'platform',
    ];

    public function player()
    {
        return $this->hasOne(Player::class, 'player_code', 'normalized_bill_ref');
    }

    public function paybill()
    {
        return $this->hasOne(PaybillWallet::class, 'shortcode', 'BusinessShortCode');
    }

    // public function platform()
    // {
    //     return $this->hasOne(Platforms::class, 'id', 'SmsShortcode');
    // }

    // Add an accessor for normalized_bill_ref
public function getNormalizedBillRefAttribute()
{
    return substr($this->BillRefNumber, 2); // Remove the first two characters (e.g., 'VT', 'WT')
}
}
