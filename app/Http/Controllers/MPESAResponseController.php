<?php

namespace App\Http\Controllers;

use App\Models\Deposits;
use App\Models\PaybillWallet;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MPESAResponseController extends Controller
{
    public function confirmation(Request $request)
    {
        // $data = json_decode($request->getContent());
        //check if player used stk
        $deposit = Deposits::where("TransID", $request->TransID)->update([
            'TransactionType' => $request->TransactionType,
            'BusinessShortCode' => $request->BusinessShortCode,
            'BillRefNumber' => $request->BillRefNumber,
            'InvoiceNumber' => $request->InvoiceNumber,
            'OrgAccountBalance' => $request->OrgAccountBalance,
            'ThirdPartyTransID' => $request->ThirdPartyTransID,
            'FirstName' => $request->FirstName,
        ]);

        //handle payment without stkpush
        if (!$deposit) {
            $deposit = Deposits::Create($request->all());
            // call transaction query to get phone number
            $paybill = $deposit->paybill;
            $dataquery = [
                "Initiator" => $paybill->initiator,
                "SecurityCredential" => $paybill->SecurityCredential,
                "CommandID" => "TransactionStatusQuery",
                "TransactionID" => $request->TransID,
                "PartyA" => $paybill->shortcode,
                "IdentifierType" => "4",
                "ResultURL" => url('') . "/api/transquery/v1/handleCallback",
                "QueueTimeOutURL" => url('') . "/api/transquery/v1/timeout",
                "Remarks" => "Verify user transaction",
                "Occasion" => "verification",
            ];
            $transquery = new DarajaApiController;
            $trans = $transquery->transQuery($dataquery, $paybill);
            return 'processing';
        } else {
            // send confirmation sms
            $voting = new VoteController;
            $vote =  $voting->addvote($deposit);
            if ($deposit->MSISDN === $deposit->player->phone) {
                $message = "Congratulations! You have voted $vote->votes to your account $deposit->BillRefNumber. you have $vote->total_votes. Keep voting to win your prize";
                sendSMS($message, $deposit->player->phone, 1);
            }
            return 'success';
        }
    }

    public function validation(Request $request)
    {
        $player = Player::where("player_code", $request->BillRefNumber)->first();
        if ($player) {
            return [
                'ResultCode' => 0,
                'ResultDesc' => 'Accept Service',
            ];
        } else {
            return [
                'ResultCode' => "C2B00012",
                'ResultDesc' => 'Rejected',
            ];
        }
    }

    public function express(Request $request)
    {
        // Log::alert($request->all());
        $data = $request->all();
        $stkCallback = $data['Body']['stkCallback'];
        if ($stkCallback['ResultCode'] !== 0) {
            return [
                'ResultCode' => 'failed',
                'ResultDesc' => 'Accept Service',
            ];
        }
        $CallbackMetadata = $stkCallback['CallbackMetadata']['Item'];
        // dd($stkCallback["ResultCode"]);
        // $result =
        // dd($result);
        try {
            $bet = Deposits::updateOrCreate(
                [
                    'MerchantRequestID' => $stkCallback['MerchantRequestID'],
                    'CheckoutRequestID' => $stkCallback['CheckoutRequestID'],
                ],
                [
                    'ResultCode' => $stkCallback['ResultCode'],
                    'TransID' => $CallbackMetadata[1]['Value'],
                    'TransTime' => $CallbackMetadata[3]['Value'],
                    'TransAmount' => $CallbackMetadata[0]['Value'],
                    'MSISDN' => $CallbackMetadata[4]['Value'],
                ]
            );
        } catch (\Throwable $th) {
            return $th;
        }

        return [
            'ResultCode' => 0,
            'ResultDesc' => 'Accept Service',
        ];
    }

    //api functions starts here
    public function transqueryCallback(Request $request)
    {
        // Extract DebitPartyName from the callback data
        // Log::info($request);
        Log::error($request);

        if ($request->json('Result.ResultCode') != 0) {
            Log::info($request);
            return 'failed';
        }
        $debitPartyName = $request->json('Result.ResultParameters.ResultParameter.0.Value');
        $TransID = $request->json('Result.ResultParameters.ResultParameter.12.Value');


        // Log::info("names");
        // Split DebitPartyName into phone number and name
        list($phoneNumber, $fullName) = explode(' - ', $debitPartyName);
        // Split names into three names
        // Split full name into first, middle, and last names
        $names = explode(' ', $fullName);
        $firstname = $names[0];
        $middlename = (count($names) > 2) ? $names[1] : null;
        $lastname = end($names);
        // list($firstname, $middlename, $lastname) = explode('   ', $names);
        // Log::info([$firstname, $middlename, $lastname]);

        $deposit = Deposits::where("TransID", $TransID)->update([
            'MSISDN' => $phoneNumber,
            'FirstName' => $firstname,
            'MiddleName' => $middlename,
            'LastName' => $lastname,
        ]);

        $voting = new VoteController;
        $vote =  $voting->addvote($deposit);
        if ($deposit->MSISDN === $deposit->player->phone) {
            $message = "Congratulations! You have voted $vote->votes to your account $deposit->BillRefNumber. you have $vote->total_votes. Keep voting to win your prize";
            sendSMS($message, $deposit->player->phone, 1);
        } else {
            $message = "Congratulations! You have $vote->votes voted for" . $deposit->player->phone . ",  account $deposit->BillRefNumber you can stand a chance to win prizes too. Get your own code by sending JOIN to 23455";
            sendSMS($message, $deposit->player->phone, 1); // await completion then 
            $message = "Congratulations. $deposit->MSISDN have voted for your account $deposit->BillRefNumber you can stand a chance to win prizes too. Get your own code by sending JOIN to 23455";
            sendSMS($message, $deposit->MSISDN, 1);
        }


        return response()->json(['message' => 'Data stored successfully']);
    }
}
