<?php

namespace App\Http\Controllers;

use App\Models\Platforms;
use App\Models\Prize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SmsController extends Controller
{
    public function receive(Request $request)
    {
        $data = $request->all();
        $message = $data['message'];
        $mobile = $data['mobile'];
        $sms_shortcode = $data['shortcode'];

        $option = strtoupper($message); // Convert to lowercase if needed

        $Contact = new ContactController;
        $contact = $Contact->store($mobile);

        if (preg_match('/^(WT|VT)([A-Z0-9]{4})$/', $option, $matches)) {
            // Extract the prefix and code
            $action = $matches[1]; // WT or VT
            $code = $matches[2];   // xxxx (wallet or vote code)

            switch ($action) {
                case 'WT':
                    if ($contact->player) {
                        $player = $contact->player;
                        return "You already have account code: $player->player_code. Vote by replying with VT$player->player_code ";
                    }

                    $platform = Platforms::whereHas('incoming', function ($query) use ($sms_shortcode) {
                        $query->where('shortcode', $sms_shortcode);
                    })->first();
            
                    // send mpesa popup
                    $DepositWallet = new DepositsController;
                    $deposit = $DepositWallet->depositfund($option, $mobile, $platform->wallet_price, $platform);
                    $sms = "To open your Wallet Enter M-Pesa pin on the prompt or send KES $platform->wallet_price to paybill: " . $platform->paybill->shortcode ." account: $option";

                    // Wallet opening logic
                    return response()->json([
                        'result_message' => $sms,
                        'result_code' => 0,
                    ]);

                case 'VT':
                    $platform = Platforms::whereHas('incoming', function ($query) use ($sms_shortcode) {
                        $query->where('shortcode', $sms_shortcode);
                    })->first();
            
                    // send mpesa popup
                    $DepositVote = new DepositsController;
                    $vote = $DepositVote->depositfund($option, $mobile, $platform->wallet_price, $platform);
                    
                    $sms = "To vote for $option Enter M-Pesa pin on the prompt or send KES $platform->vote_price to paybill: " . $platform->paybill->shortcode ." account: $option";

                    //voting logic
                    return response()->json([
                        'result_message' => $sms,
                        'result_code' => 0,
                    ]);

                default:
                    // Should not reach here because of the regex, but for safety
                    return 'Invalid action. Please try again.';
            }
        }else{
            switch ($option) {            
            case 'PRIZES':
                $prizes = Prize::where('status', 'active')->get();
                $numberedPrizes = $prizes->map(function ($prize, $index) {
                    return ($index + 1).'. '.$prize->name; // Assuming 'name' is a column in the 'prizes' table
                })->implode("\n"); // Joins all strings with a newline
                $sms = "WIN PROMO: \n \n $numberedPrizes";
                return $sms;

            default:
                // send game menu
                $menu = "Karibu CHRISTMASS promo !! \n 1. JOIN\n 2. VOTE\n 3. PRIZES\n 3.MYVOTES";

                return $menu;
            }
        }
    }



    public function ussd(Request $request)
    {
        $data = $request->all();

        Log::info($data);
        $message = strtoupper($data['USSD_STRING']) ?? null;
        $mobile = $data['MSISDN'];
        $sessionId = $data['SESSION_ID'];
        $sms_shortcode = urldecode($data['SERVICE_CODE']);
        // Log::info($sms_shortcode);

        // $option = strtoupper($message); // Convert to lowercase if needed

        $Contact = new ContactController;
        $contact = $Contact->store($mobile);

        $platform = Platforms::whereHas('incoming', function ($query) use ($sms_shortcode) {
            $query->where('shortcode', $sms_shortcode);
        })->first();

        if ($platform) {//if platform in db

            if ($message) {
                $inputs = explode('*', urldecode($message));
                $message = end($inputs); // Safely get the last value
            } else {
                $message = null;
            }

            // Retrieve or initialize the session state
            $sessionState = Cache::get("ussd_session_state_{$sessionId}", 'start');

            // Log::info('session state: '.$sessionState);

            // Step 1: Welcome message and box selection
            if (is_null($message) && $sessionState === 'start') {
                Cache::put("ussd_session_state_{$sessionId}", 'select_option');

                $sms = "CON Sherehekea Krisi na Tuzo Kubwa!\n***\n***\n1. New Wallet\n2. Refferal Wallet\n3. Vote 4. Get Share Text\n***\n *** \n Sherehekea Krisi na style!";

                return response($sms);

                // Step 2: Box selection
            } elseif ($sessionState === 'select_option') {
                $option = (int) $message;
                switch ($option) {
                    case '1': //new wallet -- send push to pay for new wallet
                        if ($contact->player) {
                            Cache::put("ussd_session_state_{$sessionId}", 'player_exist');
                            $player = $contact->player;
                            return "You already have account code: refferal at WT$player->player_code vote at VT$player->player_code. Vote \n1. For Self \n2. For Other \n3. Cancel ";
                        }
                        $wallet = "WT0000";
                        // send mpesa popup
                        $DepositWallet = new DepositsController;
                        $deposit = $DepositWallet->depositfund($wallet, $mobile, $platform->wallet_price, $platform);
                        $sms = "END To open your Wallet Enter M-Pesa pin on the prompt or send KES $platform->wallet_price to paybill: " . $platform->paybill->shortcode . " account: $wallet";
                        return response($sms);

                    case '2': //open wallet with refferal code
                        if ($contact->player) {
                            Cache::put("ussd_session_state_{$sessionId}", 'player_exist');
                            $player = $contact->player;
                            return "You already have account code: $player->player_code. Vote \n1. For Self \n2. For Other \n3. Cancel ";
                        }
                        Cache::put("ussd_session_state_{$sessionId}", 'wallet_refferer');
                        $sms = "CON Enter the refferer code:";
                        return response($sms);

                    case '3': // vote for player
                        Cache::put("ussd_session_state_{$sessionId}", 'vote_for_player');
                        $sms = "CON Enter the player code:";
                        return response($sms);
                    case '4':
                        Cache::forget("ussd_session_state_{$sessionId}");
                        if (!$contact->player) {
                            $wallet = "WT0000";
                            $DepositWallet = new DepositsController;
                            $deposit = $DepositWallet->depositfund($wallet, $mobile, $platform->wallet_price, $platform);
                            $sms = "END You need a wallet first. To open your Wallet Enter M-Pesa pin on the prompt or send KES $platform->wallet_price to paybill: " . $platform->paybill->shortcode . " account: $wallet";
                            return response($sms);
                        }
                        $wallet = $contact->player->player_code;
                        $message = "Hi! I'm in the MAISHA KRISI PROMO, and your vote can help us win BIG! 🏆\n\n" .
                        "👉 Dial *245#, select Option 3, and enter my code: $wallet.\n" .
                        "OR\n" .
                        "💵 Send KES $platform->wallet_price to Paybill: {$platform->paybill->shortcode}, Acc: $wallet.\n\n" .
                        "Let’s do this together! Share with friends so we can win amazing prizes! 🎉💪";                        
                        
                        sendSMS($message, $mobile, 1);
                        $sms = "END Your share message has been sent to you";
                        return response($sms);

                    default:
                        $sms = "END Invalid Option:";
                        return response($sms);

                }
            } elseif ($sessionState === 'player_exist') {
                $option = (int) $message;
                switch ($option) {
                    case '1': //vote for self -- send push to pay for new wallet                        
                        $wallet = $contact->player->player_code;
                        // send mpesa popup
                        $DepositVote = new DepositsController;
                        $vote = $DepositVote->depositfund($wallet, $mobile, $platform->wallet_price, $platform);

                        $sms = "END To vote for $wallet Enter M-Pesa pin on the prompt or send KES $platform->vote_price to paybill: " . $platform->paybill->shortcode . " account: $wallet";
                        return response($sms);
                    case '2': //vote for friend -- send push to pay for new wallet
                        Cache::put("ussd_session_state_{$sessionId}", 'vote_for_player');
                        $sms = "CON Enter the Players code:";
                        return response($sms);
                    case '3': //Cancel
                        $sms = "END Thank you for participating you can start over by dialing *245#.";
                        return response($sms);
                    default:
                        $sms = "END Invalid Option:";
                        return response($sms);
                }
            } elseif ($sessionState === 'vote_for_player') {
                $wallet = $message;
                // send mpesa popup
                $DepositVote = new DepositsController;
                $vote = $DepositVote->depositfund($wallet, $mobile, $platform->wallet_price, $platform);

                $sms = "END vote for $wallet Enter M-Pesa pin on the prompt or send KES $platform->vote_price to paybill: " . $platform->paybill->shortcode . " account: $wallet";
                return response($sms);

            } elseif ($sessionState === 'wallet_refferer') { //open wallet by refferer code
                $wallet = $message;
                        // send mpesa popup
                        $DepositWallet = new DepositsController;
                        $deposit = $DepositWallet->depositfund($wallet, $mobile, $platform->wallet_price, $platform);
                        $sms = "END To open your Wallet Enter M-Pesa pin on the prompt or send KES $platform->wallet_price to paybill: " . $platform->paybill->shortcode . " account: $wallet";
                        return response($sms);
            }

            else {//if invalid option
                $sms = "CON Sherehekea Krisi na Tuzo Kubwa!\n***\n***\n1. New Wallet\n2. Refferal Wallet\n3. Vote ***\n *** \n Sherehekea Krisi na style!";

                return response($sms); // invalid option
            }
        }else{
            return response('END REQUEST FAILED'); //no platform
        }
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
