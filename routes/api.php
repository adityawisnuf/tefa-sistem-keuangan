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
use App\Http\Controllers\KelasController;
use App\Http\Controllers\PendaftarDokumenController;
use App\Http\Controllers\PendaftaranAkademikController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PpdbController;
use App\Http\Controllers\TrackingPendaftaran;


Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

// Routes for authenticated users
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [LogoutController::class, 'logout']);

    
    // Pendaftar Routes
    Route::group(['prefix'=>'pendaftar', 'middleware'=> 'checkrole:OrangTua'], function () {
        Route::post('/', [PendaftarController::class, 'store']);
        Route::get('/', [PendaftarController::class, 'index']);
        Route::get('{id}', [PendaftarController::class, 'show']);
        Route::put('{id}', [PendaftarController::class, 'update']);
        Route::delete('{id}', [PendaftarController::class, 'destroy']);
    });
    
    // Pendaftaran Akademik Routes
    Route::group(['prefix'=>'pendaftar-akademik', 'middleware'=> 'checkrole:OrangTua'], function () {
        Route::post('/', [PendaftaranAkademikController::class, 'store']);
        Route::get('/', [PendaftaranAkademikController::class, 'index']);
        Route::get('{id}', [PendaftaranAkademikController::class, 'show']);
        Route::put('{id}', [PendaftaranAkademikController::class, 'update']);
        Route::delete('{id}', [PendaftaranAkademikController::class, 'destroy']);
    });
    
    Route::group(['prefix'=>'pendaftar-dokumen', 'middleware'=> 'checkrole:OrangTua'], function () {
        Route::post('/', [PendaftarDokumenController::class, 'store']);
        Route::get('/', [PendaftarDokumenController::class, 'index']);
        Route::get('{id}', [PendaftarDokumenController::class, 'show']);
        Route::put('{id}', [PendaftarDokumenController::class, 'update']);
        Route::delete('{id}', [PendaftarDokumenController::class, 'destroy']);
    });
    
    Route::group(['prefix'=>'pembayaran', 'middleware'=> 'checkrole:KepalaSekolah,Admin,Bendahara'], function () {
        Route::get('/export-pembayaran-ppdb', [PembayaranController::class, 'exportPembayaranPpdb']);
        Route::get('/laporan-keuangan', [LaporanKeuanganController::class, 'searchLaporanKeuangan']);
        
    });
    
    Route::group(['prefix'=>'email', 'middleware'=> 'checkrole:OrangTua'], function () {
        Route::post('/email-verification', [EmailVerificationController::class, 'email_verification']);
        Route::post('/send-email-verification', [EmailVerificationController::class, 'sendEmailVerification']);
    });
    
    Route::group(['prefix' => 'ppdb'], function () {
        Route::post('/', [PpdbController::class, 'store']);
        Route::get('/track', [TrackingPendaftaran::class, 'trackPendaftaran']);
        Route::get('/all/pendaftaran', [TrackingPendaftaran::class, 'searchPendaftarans']);
        Route::get('/export-pendaftar', [PendaftarController::class, 'export']);
        Route::get('download/{id}', [PpdbController::class, 'downloadDocuments']);
        Route::post('/update-status', [PpdbController::class, 'updateStatus']);
    });
    Route::prefix('kelas')->group(function () {
        Route::get('/', [KelasController::class, 'index']);
        Route::get('{id}', [KelasController::class, 'show']);
        Route::post('/', [KelasController::class, 'store']);
        Route::put('{id}', [KelasController::class, 'update']);
        Route::get('/download-berkas/{id}', [PendaftarDokumenController::class, 'mergePendaftarDokumen']);
        Route::delete('{id}', [KelasController::class, 'destroy']);
        
    });
    
    Route::prefix('payment')->group(function () {
        Route::post('/', [PembayaranController::class, 'createTransaction']);
        Route::get('/get', [PembayaranController::class, 'getPaymentMethod']);
    });
    
    Route::group(['prefix' => 'amounts'], function () {
        Route::get('/', [AmountController::class, 'index']);
        Route::post('/get', [AmountController::class, 'store']);
        Route::get('/{id}', [AmountController::class, 'show']);
        Route::put('/{id}', [AmountController::class, 'update']);
        Route::delete('/{id}', [AmountController::class, 'destroy']);
    });
});



Route::get('get-province', [IndoRegionController::class, 'getAllProvinces']);
Route::get('get-regency/{provinceId}', [IndoRegionController::class, 'getRegenciesByProvince']);
Route::get('get-district/{regencyId}', [IndoRegionController::class, 'getDistrictsByRegency']);
Route::get('get-village/{districtId}', [IndoRegionController::class, 'getVillagesByDistrict']);

Route::post('/validate-nik', [NIKController::class, 'validateNik']);
Route::post('/payment-callback', [PembayaranController::class, 'handleCallback']);
Route::get('test');
