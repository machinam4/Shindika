<?php

namespace App\Http\Controllers;

use App\Models\Deposits;
use Illuminate\Http\Request;

class DepositsController extends Controller
{
    public function depositfund($box, $phoneNumber, $platform)
    {
        $stakeAmount = $stakeAmount ?? $platform->bet_minimum;

        // Log::info($platform->paybill);
        // stk push
        $paybill = $platform->paybill;
        $timestamp = now()->setTimezone('UTC')->format('YmdHis');
        $data = [
            'BusinessShortCode' => $platform->paybill->shortcode,
            'Password' => base64_encode($paybill->shortcode . $paybill->passkey . $timestamp),
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $stakeAmount,
            'PartyA' => $phoneNumber,
            'PartyB' => $paybill->shortcode,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => url('') . '/api/c2b/express',
            'AccountReference' => "Box $box",
            'TransactionDesc' => 'Lucky Box ' . $box,
        ];
        // Log::info($data);
        // Log::info(response()->json($data, 200));

        // TO:DO wait for mpesa to finish transaction the send notifi to user
        try {
            $sendStk = new DarajaApiController;
            $response = $sendStk->STKPush($data, $paybill);
            // Log::info(json_encode($response));
            if (! isset($response->ResponseCode)) {
                return response()->json('failed', 200);
            }
            if ($response->ResponseCode !== '0') {
                return response()->json('failed', 200);
            } else {
                $res_data = [
                    // "ResultCode" => $response->ResultCode,
                    'MerchantRequestID' => $response->MerchantRequestID,
                    'CheckoutRequestID' => $response->CheckoutRequestID,
                    'TransactionType' => 'CustomerPayBillOnline',
                    'BusinessShortCode' => $paybill->shortcode,
                    'BillRefNumber' => "Box $box",
                    'MSISDN' => $phoneNumber,
                    'SmsShortcode' => $platform->id,
                ];
                // Log::info($res_data);
                Deposits::Create($res_data);
                // Log::info('stk sent');
            }

            // session()->flush();
            // dd($response);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
