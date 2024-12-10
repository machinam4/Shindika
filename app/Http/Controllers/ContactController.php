<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return Contact::all();
    }
    public function store($phoneNumber, $step = 0)
    {
        $phone = Contact::firstOrCreate(
            ["phone" => $phoneNumber],
            ["step" => $step]
        );
        return $phone;
    }
}
