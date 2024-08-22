<?php

use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\IndoRegionController;
use App\Http\Controllers\LaporanKeuanganController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PendaftarController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\NIKController;
use App\Http\Controllers\AmountController;
use App\Http\Controllers\PdfDownloadController;
use App\Http\Controllers\PendaftarDokumenController;
use App\Http\Controllers\PendaftaranAkademikController;
use App\Http\Controllers\PendaftarKomplitController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PpdbController;
use App\Http\Controllers\TrackingPendaftaran;
use App\Models\PendaftarDokumen;


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

    Route::group(['prefix' => 'ppdb'], function () {
        Route::get('/track', [TrackingPendaftaran::class, 'trackPendaftaran']);
        Route::get('/all/pendaftaran', [TrackingPendaftaran::class, 'getAllPendaftarans']);
        Route::get('/search', [TrackingPendaftaran::class, 'searchPendaftarans']);
    });





    Route::post('/payment', [PembayaranController::class, 'createTransaction']);
});
// Route for Pendaftar Komplit (no auth required)
Route::group(['prefix' => 'amounts'], function () {
    Route::get('/', [AmountController::class, 'index']);
    Route::post('/get', [AmountController::class, 'store']);
    Route::get('/{id}', [AmountController::class, 'show']);
    Route::put('/{id}', [AmountController::class, 'update']);
    Route::delete('/{id}', [AmountController::class, 'destroy']);
});
Route::get('get-province', [IndoRegionController::class, 'getAllProvinces']);
Route::get('get-regency/{provinceId}', [IndoRegionController::class, 'getRegenciesByProvince']);
Route::get('get-district/{regencyId}', [IndoRegionController::class, 'getDistrictsByRegency']);
Route::get('get-village/{districtId}', [IndoRegionController::class, 'getVillagesByDistrict']);
Route::post('pendaftar-komplit', [PendaftarKomplitController::class, 'store']);

Route::post('/update-status', [PpdbController::class, 'updateStatus']);
Route::get('download/{id}', [PpdbController::class, 'downloadDocuments']);

Route::get('/payment-get', [PembayaranController::class, 'getPaymentMethod']);
Route::post('/payment-method', [PembayaranController::class, 'getPaymentMethod']);
Route::post('/payment-callback', [PembayaranController::class, 'handleCallback']);

Route::get('/download-berkas/{id}', [PendaftarDokumenController::class, 'mergePendaftarDokumen']);
Route::post('/validate-nik', [NIKController::class, 'validateNik']);
Route::get('/laporan', [LaporanKeuanganController::class, 'laporanKeuangan']);
// api.php
Route::get('/export-pendaftar', [PendaftarController::class, 'export']);
Route::get('export-pembayaran-ppdb', [PembayaranController::class, 'exportPembayaranPpdb']);
Route::get('filter-ppdb', [PpdbController::class, 'filterByStatusAndYear']);
Route::get('total-pendaftar', [PpdbController::class, 'getTotalPendaftar']);
Route::get('test');


    // Route::post('/download-pdf', [PdfDownloadController::class, 'store']);
