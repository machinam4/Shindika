<?php

namespace App\Http\Controllers;

use App\Models\Deposits;

class DepositsController extends Controller
{
    public function depositfund($playercode, $phoneNumber, $amount, $platform)
    {
        // $stakeAmount = $stakeAmount ?? $platform->bet_minimum;
        $paybill = $platform->paybill;


        // Log::info($platform->paybill);
        // stk push
        $timestamp = now()->setTimezone('UTC')->format('YmdHis');
        $data = [
            'BusinessShortCode' => $paybill->shortcode,
            'Password' => base64_encode($paybill->shortcode.$paybill->passkey.$timestamp),
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phoneNumber,
            'PartyB' => $paybill->shortcode,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => url('').'/api/c2b/express',
            'AccountReference' => "WALLET $playercode",
            'TransactionDesc' => "WALLET $playercode",
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
                    'BillRefNumber' => "Box $playercode",
                    'MSISDN' => $phoneNumber,
                    'platform' => $platform->id,
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
