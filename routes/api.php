<?php

use App\Http\Controllers\BendaharaController;
use App\Http\Controllers\BendaharaLaporanController;
use App\Http\Controllers\BendaharaPengajuanController;
use App\Http\Controllers\KepsekLaporanController;
use App\Http\Controllers\KepsekPengajuanController;
use App\Http\Controllers\LaundryTransaksiController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\UsahaPengajuanController;
use App\Http\Controllers\KantinProdukController;
use App\Http\Controllers\KantinProdukKategoriController;
use App\Http\Controllers\KantinTransaksiController;
use App\Http\Controllers\LaundryLayananController;
use App\Http\Controllers\SiswaKantinController;
use App\Http\Controllers\SiswaLaundryController;
use App\Http\Controllers\SiswaWalletController;
use App\Http\Controllers\TopUpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use Illuminate\Support\Facades\Auth;

// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::post('/duitku/callback', [TopUpController::class, 'callback']);

Route::group([
    'middleware' => ['auth:api']
], function () {
    Route::post('logout', [LogoutController::class, 'logout']);

    Route::post('/duitku/get-payment-method', [TopUpController::class, 'getPaymentMethod']);
    Route::post('/duitku/request-transaksi', [TopUpController::class, 'requestTransaction']);

    Route::group([
        'prefix' => 'orangtua',
        'middleware' => 'checkrole:OrangTua'
    ], function () {
        Route::group(['prefix' => 'siswa'], function() {
            Route::get('/', [OrangTuaController::class, 'getSiswa']);

            Route::group(['prefix' => 'riwayat'], function() {
                Route::get('/wallet/{id}', [OrangTuaController::class, 'getRiwayatWalletSiswa']);
                Route::get('/transaksi/{id}', [OrangTuaController::class, 'getRiwayatTransaksiSiswa']);
            });
        });
    });

    Route::group([
        'prefix' => 'siswa',
        'middleware' => 'checkrole:Siswa'
    ], function () {
        Route::group(['prefix' => 'wallet'], function () {
            Route::get('/', [SiswaWalletController::class, 'getSaldo']);
            Route::get('/riwayat', [SiswaWalletController::class, 'getRiwayat']);
        });

        Route::group(['prefix' => 'kantin'], function () {
            Route::group(['prefix' => 'produk'], function () {
                Route::get('/', [SiswaKantinController::class, 'getProduk']);
                Route::get('/riwayat', [SiswaKantinController::class, 'getKantinRiwayat']);
                Route::post('/transaksi', [SiswaKantinController::class, 'createProdukTransaksi']);
                Route::get('/{id}', [SiswaKantinController::class, 'getProdukDetail']);
            });
        });

        Route::group(['prefix' => 'laundry'], function () {
            Route::group(['prefix' => 'layanan'], function () {
                Route::get('/', [SiswaLaundryController::class, 'getLayanan']);
                Route::get('/riwayat', [SiswaLaundryController::class, 'getLayananRiwayat']);
                Route::get('/transaksi', [SiswaLaundryController::class, 'getLayananTransaksi']);
                Route::post('/transaksi/create', [SiswaLaundryController::class, 'createLayananTransaksi']);
                Route::get('/{id}', [SiswaLaundryController::class, 'getLayananDetail']);
            });
        });
    });

    Route::group([
        'prefix' => 'kantin',
        'middleware' => 'checkrole:Kantin'
    ], function () {
        //produk crud
        Route::group(['prefix' => 'produk'], function () {
            Route::get('/', [KantinProdukController::class, 'index']);
            Route::post('/', [KantinProdukController::class, 'create']);
            Route::get('/{id}', [KantinProdukController::class, 'show']);
            Route::put('/{id}', [KantinProdukController::class, 'update']);
            Route::delete('/{id}', [KantinProdukController::class, 'destroy']);
        });

        //kategori crud
        Route::group(['prefix' => 'kategori'], function () {
            Route::get('/', [KantinProdukKategoriController::class, 'index']);
            Route::post('/', [KantinProdukKategoriController::class, 'create']);
            Route::get('/{id}', [KantinProdukKategoriController::class, 'show']);
            Route::put('/{id}', [KantinProdukKategoriController::class, 'update']);
            Route::delete('/{id}', [KantinProdukKategoriController::class, 'destroy']);
        });

        //transaksi
        Route::group(['prefix' => 'transaksi'], function () {
            Route::get('/', [KantinTransaksiController::class, 'index']);
            Route::post('/{id}', [KantinTransaksiController::class, 'update']);
            Route::put('/{id}/konfrmasi', [KantinTransaksiController::class, 'confirm']);
        });

        //pengajuan
        Route::group(['prefix' => 'pengajuan'], function () {
            Route::post('/create', [UsahaPengajuanController::class, 'create']);
            Route::get('/', [UsahaPengajuanController::class, 'index']);
        });
    });

    Route::group([
        'prefix' => 'laundry',
        'middleware' => 'checkrole:Laundry'
    ], function () {
        Route::group(['prefix' => 'layanan'], function () {
            Route::get('/', [LaundryLayananController::class, 'index']);
            Route::post('/', [LaundryLayananController::class, 'create']);
            Route::get('/{id}', [LaundryLayananController::class, 'show']);
            Route::put('/{id}', [LaundryLayananController::class, 'update']);
            Route::delete('/{id}', [LaundryLayananController::class, 'destroy']);
        });

        Route::group(['prefix' => 'transaksi'], function () {
            Route::get('/', [LaundryTransaksiController::class, 'getTransaction']);
            Route::get('/{id}', [LaundryTransaksiController::class, 'getDetailUsahaTransaksi']);
            Route::post('/{id}', [LaundryTransaksiController::class, 'update']);
            Route::put('/{id}/konfirmasi', [LaundryTransaksiController::class, 'confirmInitialTransaction']);
        });

        Route::group(['prefix' => 'pengajuan'], function () {
            Route::post('/create', [UsahaPengajuanController::class, 'create']);
            Route::get('/', [UsahaPengajuanController::class, 'index']);
        });
    });

    Route::group([
        'prefix' => 'bendahara',
        'middleware' => 'checkrole:Bendahara'
    ], function () {
        Route::get('/laporan', [BendaharaLaporanController::class, 'getUsahaTransaksi']);
        Route::get('/pengajuan', [BendaharaPengajuanController::class, 'getUsahaPengajuan']);
        Route::put('/pengajuan/{id}', [BendaharaPengajuanController::class, 'confirmUsahaPengajuan']);
    });

    Route::group([
        'prefix' => 'kepsek',
        'middleware' => 'checkrole:KepalaSekolah'
    ], function () {
        Route::get('/laporan', [KepsekLaporanController::class, 'getUsahaTransaksi']);
        Route::get('/laporan/{id}', [KepsekLaporanController::class, 'getDetailUsahaTransaksi']);
        Route::get('/pengajuan', [KepsekPengajuanController::class, 'getUsahaPengajuan']);
    });
});

Route::post('/test', function (Request $request) {
    $siswa = Auth::user()->usaha->firstOrFail();
    return $siswa;
})->middleware('auth:api');