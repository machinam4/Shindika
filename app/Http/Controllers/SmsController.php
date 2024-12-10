<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Prize;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Break_;

class SmsController extends Controller
{
    public function receive(Request $request)
    {
        $data = $request->all();
        $message = $data['message'];
        $mobile = $data['mobile'];
        $sms_shortcode = $data['shortcode'];

        $option = strtoupper($message); // Convert to lowercase if needed

        $Contact = new ContactController();
        $contact = $Contact->store($mobile);

        switch ($option) {
            case 'SHINDA': //case 0
                // send game menu
                $menu = "Karibu CHRISTMASS promo !! \n 1. JOIN\n 2. VOTE\n 3. PRIZES\n 3.MYVOTES";
                return $menu;

                // case 1 ..wallet without refferer
            case 'WALLET':
                $menu = "To open a WALLET reply with the word JOIN follwed by your refferer code.";
                return $menu;

                //case 2, join with refferer
            case preg_match('/^WALLET (\w{6,})$/', $option, $matches):
                $referrerCode = $matches[1];
                $wallet_amount = 100;
                $paybill = 79796;
                if ($contact->player) {
                    $player = $contact->player;
                    return "You already have account code: $player->player_code. Vote by sending word VOTE $player->player_code ";
                }
                // send mpesa popup


                $sms = "To open your Wallet Enter M-Pesa pin on the prompt or send KES $wallet_amount to paybill $paybill account: $referrerCode";
                break;

            case 'VOTE':
                $wallet_amount = 100;
                $paybill = 79796;
                $account = "obtain account";
                $menu = "To vote reply with VOTE CODE .sSend KES $wallet_amount to paybill $paybill account: $account if popup does not appear";
                return $menu;

            case 'VOTE':
            case 2:
                $wallet_amount = 100;
                $paybill = 79796;
                $sms = "Reply with VOTE ACCOUNT \n \n Where ACCOUNT is the code for the one you are voting for.\n \nyou can also send KES $wallet_amount to paybill $paybill account: code";
                break;
            case 'PRIZES':
            case 3:
                $wallet_amount = 100;
                $paybill = 79796;
                $prizes = Prize::where("status", "active")->get();
                $numberedPrizes = $prizes->map(function ($prize, $index) {
                    return ($index + 1) . '. ' . $prize->name; // Assuming 'name' is a column in the 'prizes' table
                })->implode("\n"); // Joins all strings with a newline
                $sms = "WIN PROMO: \n \n $numberedPrizes";
                break;

            default:
                break;

                return response()->json([
                    'message' => 'Invalid role specified.'
                ], 400);
        }

        return response()->json($sms . "\n 99. MENU");


        // Log::info($sms_shortcode);
        // Check if the message contains the keyword "Box"
        $box = strtolower($message); // Convert to lowercase if needed
        if ($box == 'box') {
            // Perform actions based on the content of the message
            // You can customize this part to perform any specific actions you need.
            // Log::info('Received SMS without keyword "Box": ' . $message);
            // Respond to the SMS
            $sms = "Karibu LUCKYBOX!\n**\nPESA TASLIMU zimewekwa kwenye BOX TANO.\n**\nBox 1\nBox 2\nBox 3\nBox 4\nBox 5\n**\nChomoka na PESA.Tuma chaguo lako kwa " . $sms_shortcode . " USHINDE sasa hivi!\nSTOP?*456*9*5#";
            // $SMS = new SMSController;
            // $SMS = new LidenController;
            // $sendSMS = $SMS->sendSMS($sms, $phoneNumber);

            return response()->json([
                'result_message' => $sms,
                'result_code' => 0,
            ]);
        } elseif (preg_match("/^(box\s?[1-5]|^[1-5])$/i", $box, $matches)) { // Use a regular expression to match "box 1" to "box 5" or values from 1 to 5 in a case-insensitive way

            // $sms = 'Ujumbe wa M-Pesa utatumwa kwenye simu yako muda mfupi ujao. Thibitisha malipo ya KES 30 ili kushiriki.';
            // $SMS = new LidenController;
            // $sendSMS = $SMS->sendSMS($sms, $phoneNumber);

            // Extract and convert the integer part
            if (preg_match("/(\d+)/", $matches[0], $intMatches)) {
                $intValue = (int) $intMatches[0];
            }
            //    echo $intValue; // Output: "3"

        } else {
            // If the keyword "Box" is not found, provide a generic response
            // Log::info('Received SMS without keyword "Box": ' . $message);
            // Respond to the SMS
            $sms = "Umekosea!.\n**\nUlichagua $message.\n**\nCheza kwa kuchagua NUMBER (1-5).\n**\nMfano: 1\n**\nChagua TENA USHINDE!\n1:BOX 1\n2:BOX 2\n3:BOX 3\n4:BOX4\n5:BOX5\n**\**\nACC Bal: 0!\nSTOP*456*9*5#\n";

            return response()->json([
                'result_message' => $sms,
                'result_code' => 0,
            ]);
        }

        return response()->json('its okay', 200);
        // $sms = 'Ujumbe wa M-Pesa utatumwa kwenye simu yako muda mfupi ujao. Tafadhali thibitisha malipo ya KES 30 ili kushiriki.';

        // return response()->json([
        //     'result_message' => $sms,
        //     'result_code' => 0,
        // ]);

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
