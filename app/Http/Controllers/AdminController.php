<?php

namespace App\Http\Controllers;

use App\Models\Platforms;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function createPlatform(Request $request){
        $platform = Platforms::firstOrCreate($request->all());
        return $platform;
    }
}
