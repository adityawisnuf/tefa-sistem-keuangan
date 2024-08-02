<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PendaftarController;
use App\Http\Controllers\LogoutController;




Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);
Route::post('/pendaftar', [PendaftarController::class, 'store']);

Route::group([
    'middleware' => ['auth:api']
], function () {
    Route::post('logout', [LogoutController::class, 'logout']);

});
