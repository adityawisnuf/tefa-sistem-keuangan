<?php

use App\Http\Controllers\ArusKasController;
use App\Http\Controllers\LabaRugiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\RasioKeuanganController;

// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::group([
    'middleware' => ['auth:api']
], function () {
    Route::get('laba-rugi', [LabaRugiController::class, 'index']);
    Route::get('arus-kas', [ArusKasController::class, 'index']);
    Route::get('rasio-keuangan', [RasioKeuanganController::class, 'retrieveFinancialData']);
    Route::get('get-options', [LabaRugiController::class, 'getOptions']);
    Route::post('logout', [LogoutController::class, 'logout']);

});
