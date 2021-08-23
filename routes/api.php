<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function(){

    Route::get('/curp-info/{curp}', [\App\Http\Controllers\CurpController::class, 'getCurpInfo']);
    Route::get('/rfc-validation/{rfc}', [\App\Http\Controllers\RfcController::class, 'validateRfc']);

});

Route::get('/get-new-token/{user}', [\App\Http\Controllers\AccessTokenController::class, 'getNewToken']);

