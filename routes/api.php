<?php

use App\Http\Controllers\LaundryItemController;
use App\Http\Controllers\TopUpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;

// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::group([
    'middleware' => ['auth:api']
], function () {
    Route::post('logout', [LogoutController::class, 'logout']);
});

//kantin & laundry
Route::group([
    'prefix' => 'duitku'
], function() {
    Route::get('get-payment-method', [TopUpController::class, 'getPaymentMethod']);
    Route::post('request-transaksi', [TopUpController::class, 'requestTransaction']);
    Route::post('callback', [TopUpController::class, 'callback']);
});


Route::group([
    'prefix' => 'laundry'
], function() {
    Route::group(['prefix' => 'item'], function() {
        Route::get('/', [LaundryItemController::class, 'index']);
        Route::post('/', [LaundryItemController::class, 'create']);
        Route::put('/{item}', [LaundryItemController::class, 'update']);
        Route::delete('/{item}', [LaundryItemController::class, 'destroy']);
    });
});