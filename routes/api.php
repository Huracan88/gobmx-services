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

Route::post('/cfdi-validation', [\App\Http\Controllers\CfdiValidationController::class, 'getCfdiValidation']);

Route::middleware('auth:sanctum')->group(function(){

    Route::get('/curp-info/{curp}', [\App\Http\Controllers\CurpController::class, 'getCurpInfo']);
    Route::get('/rfc-validation/{rfc}', [\App\Http\Controllers\RfcController::class, 'validateRfc']);
//    Route::post('/cfdi-validation', [\App\Http\Controllers\CfdiValidationController::class, 'getCfdiValidation']);

    Route::post('/cfdi-sentre-tramite', [\App\Http\Controllers\CfdiValidationController::class, 'getCfdiValidation']);
    Route::post('/sentre-27', [\App\Http\Controllers\SentreController::class, 'getListadoDocumental']);


    Route::get('/sipoa', [\App\Http\Controllers\SipoaController::class, 'test']);
});

Route::get('/get-new-token/{user}', [\App\Http\Controllers\AccessTokenController::class, 'getNewToken']);

