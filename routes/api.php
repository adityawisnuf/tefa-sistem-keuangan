<?php

use App\Http\Controllers\EmailVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PendaftarController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PendaftarDokumenController;
use App\Http\Controllers\PendaftaranAkademikController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PpdbController;
use App\Http\Requests\Auth\EmailVerificationRequest;
use App\Models\Ppdb;
use Ichtrojan\Otp\Models\Otp;

// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);
// Otp::generate('$id,$digits = 10, int $validity = 15');
Route::group(['middleware' => ['auth:api']], function () {
    Route::post('logout', [LogoutController::class, 'logout']);
    
    Route::post('ppdb', [PpdbController::class, 'store']);
    Route::post('/pendaftar', [PendaftarController::class, 'store']);
    Route::get('/pendaftar', [PendaftarController::class, 'index']);
    Route::get('/pendaftar/{id}', [PendaftarController::class, 'show']);
    Route::put('/pendaftar/{id}', [PendaftarController::class, 'update']);
    Route::delete('/pendaftar/{id}', [PendaftarController::class, 'destroy']);
    
    Route::post('pendaftar-akademik', [PendaftaranAkademikController::class, 'store']);
    Route::get('pendaftar-akademik', [PendaftaranAkademikController::class, 'index']);
    Route::get('pendaftar-akademik/{id}', [PendaftaranAkademikController::class, 'show']);
    Route::put('pendaftar-akademik/{id}', [PendaftaranAkademikController::class, 'update']);
    Route::delete('pendaftar-akademik/{id}', [PendaftaranAkademikController::class, 'destroy']);
    
    Route::post('/pendaftar-dokumen', [PendaftarDokumenController::class, 'store']);
    Route::get('/pendaftar-dokumen', [PendaftarDokumenController::class, 'index']);
    Route::get('/pendaftar-dokumen/{id}', [PendaftarDokumenController::class, 'show']);
    Route::put('/pendaftar-dokumen/{id}', [PendaftarDokumenController::class, 'update']);
    Route::delete('/pendaftar-dokumen/{id}', [PendaftarDokumenController::class, 'destroy']);
    
    Route::post('email-verification', [EmailVerificationController::class, 'email_verification']);
    Route::post('send-email-verification', [EmailVerificationController::class, 'sendEmailVerification']);

    Route::get('/payment', [PembayaranController::class, 'payment']);
});

