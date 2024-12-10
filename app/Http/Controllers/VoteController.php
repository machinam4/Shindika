<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function addvote($deposit, $player_code)
    {
        $voteprice = 49;

        $votes = floor($deposit->TransAmount / $voteprice);
        $totalvotes = $deposit->player->votes()->latest()->first()->total_votes + $votes;

        $vote = Vote::Create([
            "transaction_id" => $deposit->TransID,
            "player_code" => $player_code,
            "votes" => $votes,
            "total_votes" => $totalvotes
        ]);

        if ($deposit->MSISDN === $deposit->player->phone) {
            $message = "Congratulations! You have voted $vote->votes votes to your account $deposit->BillRefNumber. you have $vote->total_votes votes. Keep voting to win your prize";
            sendSMS($message, $deposit->player->phone, 1);
        } else {
            $message = "Congratulations. $deposit->MSISDN have voted for your account $deposit->BillRefNumber you have $vote->total_votes votes. Keep voting to win your prize";
sendSMS($message, $deposit->player->phone, 1); // await completion then 
                        $message = "Congratulations! You have $vote->votes votes voted for" . $deposit->player->phone . ",  account $deposit->BillRefNumber you can stand a chance to win prizes too. Get your own code by sending JOIN to 23455";
            sendSMS($message, $deposit->MSISDN, 1);
        }

        return $vote;
    }
}
