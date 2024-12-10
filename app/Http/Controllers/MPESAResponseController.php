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
        $deposit = Deposits::where("TransID", $request->TransID)->first();

        if ($deposit) {
            $deposit->update([
                'TransactionType' => $request->TransactionType,
                'BusinessShortCode' => $request->BusinessShortCode,
                'BillRefNumber' => $request->BillRefNumber,
                'InvoiceNumber' => $request->InvoiceNumber,
                'OrgAccountBalance' => $request->OrgAccountBalance,
                'ThirdPartyTransID' => $request->ThirdPartyTransID,
                'FirstName' => $request->FirstName,
            ]);
            // add votes
            $player_code = substr($deposit->BillRefNumber, 2);
            $voting = new VoteController;
            $vote =  $voting->addvote($deposit, $player_code);
            return 'success';
        }
        //handle payment without stkpush
        // if (!$deposit) {
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
        // } 
    }

    public function validation(Request $request)
    {
        return [
            'ResultCode' => "C2B00012",
            'ResultDesc' => 'Rejected',
        ];
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
        // Log::error($request);

        if ($request->json('Result.ResultCode') != 0) {
            // Log::info($request);
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

        $deposit = Deposits::where('TransID', $TransID)->first();



        if ($deposit) {
            $deposit->update([
                'MSISDN' => $phoneNumber,
                'FirstName' => $firstname,
                'MiddleName' => $middlename,
                'LastName' => $lastname,
            ]);
            if (preg_match('/^(WT|VT)([A-Z0-9]{4})$/', strtoupper($deposit->BillRefNumber), $matches)) {
                // Extract the prefix and code
                $action = $matches[1]; // WT or VT
                $code = $matches[2];   // xxxx (wallet or vote code)

                switch ($action) {
                    case 'VT':
                        $voting = new VoteController;
                        $vote =  $voting->addvote($deposit, $code);

                        return "vote success";
                    case 'WT':
                        $player = Player::create([
                            "phone" => formatPhoneNumber($deposit->MSISDN),
                            "invite_code" => $code,
                            "transaction_id" => $deposit->TransID
                        ]);

                        $message = "Congratulations! You voting code is VT$player->player_code. Invite your friends to vote for you. You can also invite them to stand a chance too usin code WT$player->player_code. Keep voting to win your prize";
                        sendSMS($message, $deposit->MSISDN, 1);

                        return "wallet success";

                    default:
                        return "accepted";
                }
            }
        }
        return response()->json(['message' => 'Data stored successfully']);
    }
}
