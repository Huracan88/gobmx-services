<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sentre', [\App\Http\Controllers\SentreViewController::class, 'index'])->name('sentre.index');
Route::get('/sentre/{id}', [\App\Http\Controllers\SentreViewController::class, 'show'])->name('sentre.show');
Route::post('/sentre/{id}', [\App\Http\Controllers\SentreViewController::class, 'update'])->name('sentre.update');
