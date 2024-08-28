<?php

use App\Http\Controllers\Api\PembayaranSiswaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;

// Public Routes
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

// Authenticated Routes
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [LogoutController::class, 'logout']);

    // Role: BENDAHARA
    Route::middleware('checkrole:Bendahara')->prefix('bendahara')->group(function () {
        Route::get('/pembayaran-siswa', [PembayaranSiswaController::class, 'index']);
    });

    // Role: SISWA
    Route::middleware('checkrole:Siswa')->prefix('siswa')->group(function () {
        Route::get('/pembayaran-siswa', [PembayaranSiswaController::class, 'index']);
        Route::patch('/pembayaran-siswa/{id}', [PembayaranSiswaController::class, 'update']);
        Route::get('/riwayat-pembayaran', [PembayaranSiswaController::class, 'riwayatPembayaran']);
        Route::get('/riwayat-tagihan', [PembayaranSiswaController::class, 'riwayatTagihan']);
    });

    // Role: ORANG TUA
    Route::middleware('checkrole:Orang Tua')->prefix('orangtua')->group(function () {
        Route::get('/pembayaran-siswa', [PembayaranSiswaController::class, 'index']);
        Route::post('/pembayaran-siswa/{id}/bayar', [PembayaranSiswaController::class, 'bayar']);
        Route::get('/riwayat-pembayaran', [PembayaranSiswaController::class, 'riwayatPembayaran']);
        Route::get('/riwayat-tagihan', [PembayaranSiswaController::class, 'riwayatTagihan']);
    });
});