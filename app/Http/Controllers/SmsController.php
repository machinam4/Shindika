<?php

namespace App\Http\Controllers;

use App\Models\Platforms;
use App\Models\Prize;
use Illuminate\Http\Request;

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
                break;

            default:
                // send game menu
                $menu = "Karibu CHRISTMASS promo !! \n 1. JOIN\n 2. VOTE\n 3. PRIZES\n 3.MYVOTES";

                return $menu;
            }
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
