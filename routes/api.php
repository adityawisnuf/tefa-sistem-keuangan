<?php

use App\Http\Controllers\EmailVerificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PendaftarController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PendaftarDokumenController;
use App\Http\Controllers\PendaftaranAkademikController;
use App\Http\Controllers\PendaftarKomplitController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PpdbController;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

// Routes for authenticated users
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [LogoutController::class, 'logout']);

    Route::post('ppdb', [PpdbController::class, 'store']);

    // Pendaftar Routes
    Route::prefix('pendaftar')->group(function () {
        Route::post('/', [PendaftarController::class, 'store']);
        Route::get('/', [PendaftarController::class, 'index']);
        Route::get('{id}', [PendaftarController::class, 'show']);
        Route::put('{id}', [PendaftarController::class, 'update']);
        Route::delete('{id}', [PendaftarController::class, 'destroy']);
    });

    // Pendaftaran Akademik Routes
    Route::prefix('pendaftar-akademik')->group(function () {
        Route::post('/', [PendaftaranAkademikController::class, 'store']);
        Route::get('/', [PendaftaranAkademikController::class, 'index']);
        Route::get('{id}', [PendaftaranAkademikController::class, 'show']);
        Route::put('{id}', [PendaftaranAkademikController::class, 'update']);
        Route::delete('{id}', [PendaftaranAkademikController::class, 'destroy']);
    });

    // Pendaftar Dokumen Routes
    Route::prefix('pendaftar-dokumen')->group(function () {
        Route::post('/', [PendaftarDokumenController::class, 'store']);
        Route::get('/', [PendaftarDokumenController::class, 'index']);
        Route::get('{id}', [PendaftarDokumenController::class, 'show']);
        Route::put('{id}', [PendaftarDokumenController::class, 'update']);
        Route::delete('{id}', [PendaftarDokumenController::class, 'destroy']);
    });

    // Email Verification Routes
    Route::post('email-verification', [EmailVerificationController::class, 'email_verification']);
    Route::post('send-email-verification', [EmailVerificationController::class, 'sendEmailVerification']);

    // Pembayaran Routes


});

// Route for Pendaftar Komplit (no auth required)
Route::post('pendaftar-komplit', [PendaftarKomplitController::class, 'store']);

Route::post('/payment', [PembayaranController::class, 'createTransaction']);
Route::post('/payment-method', [PembayaranController::class, 'getPeymentMethod']);
Route::post('/payment-callback', [PembayaranController::class, 'handleCallback']);
Route::post('/payment-get', [PembayaranController::class, 'getPaymentMethod']);

