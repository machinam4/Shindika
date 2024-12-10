<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function addvote($data)
    {
        $voteprice = 49;

        $vote = Vote::Create([
            "transaction_id" => $data->TransID,
            "player_code" => $data->BillRefNumber,
            "votes" => floor($data->TransAmount / $voteprice),
        ]);

        return $vote;
    }
}
