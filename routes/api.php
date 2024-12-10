<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\MPESAResponseController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\SmsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('players', PlayerController::class);
Route::apiResource('contacts', ContactController::class);
Route::post('/sms/receive', [SmsController::class, 'receive']);



Route::post('/c2b/v1/confirmation', [MPESAResponseController::class, 'confirmation']);
Route::post('/c2b/v1/validation', [MPESAResponseController::class, 'validation']);
Route::post('/c2b/v1/express', [MPESAResponseController::class, 'express']);


Route::post('/transquery/v1/handleCallback', [MPESAResponseController::class, 'transquery']);
