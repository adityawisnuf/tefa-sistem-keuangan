<?php

use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\PembayaranSiswaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::post('duitku/callback', [PembayaranController::class, 'duitkuCallbackHandler'])->name('payment.transaction.callback');
// Public Routes
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

// Authenticated Routes
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [LogoutController::class, 'logout']);

    Route::prefix('payment')->group(function () {
        Route::get('/me', [PembayaranController::class, 'getCurrent'])->name('payment.transaction.request');
        Route::get('/', [PembayaranController::class, 'getRiwayat'])->name('payment.transaction.request');
        Route::get('methods/{id}', [PembayaranController::class, 'getPaymentMethod'])->name('payment.methods');
        Route::post('transaction/request', [PembayaranController::class, 'requestTransaksi'])->name('payment.transaction.request');
        Route::post('cancel/{merchant_order_id}', [PembayaranController::class, 'batalTransaksi'])->name('payment.transaction.request');
    });

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
