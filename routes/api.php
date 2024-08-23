<?php

use App\Http\Controllers\BendaharaController;
use App\Http\Controllers\BendaharaPengajuanController;
use App\Http\Controllers\LaundryTransaksiController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\OrangTuaRiwayatController;
use App\Http\Controllers\OrangTuaSiswaController;
use App\Http\Controllers\OrangTuaWalletController;
use App\Http\Controllers\UsahaPengajuanController;
use App\Http\Controllers\KantinProdukController;
use App\Http\Controllers\KantinProdukKategoriController;
use App\Http\Controllers\KantinTransaksiController;
use App\Http\Controllers\KepsekController;
use App\Http\Controllers\LaundryItemController;
use App\Http\Controllers\LaundryLayananController;
use App\Http\Controllers\LaundryPengajuanController;
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

    Route::post('/duitku/get-payment-method', [TopUpController::class, 'getPaymentMethod'])->name('get-payment-method');
    Route::post('/duitku/request-transaksi', [TopUpController::class, 'requestTransaction'])->name('request-transakepseksi');

    Route::group([
        'prefix' => 'orangtua',
        'middleware' => 'checkrole:OrangTua'
    ], function () {
        Route::get('/siswa', [OrangTuaSiswaController::class, 'getDataSiswa']);
        Route::get('/riwayat', [OrangTuaRiwayatController::class, 'getRiwayatSiswa']);
        Route::get('/wallet', [OrangTuaWalletController::class, 'getWalletSiswa']);
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
                Route::get('/{produk}', [SiswaKantinController::class, 'getProdukDetail']);
                Route::get('/riwayat', [SiswaKantinController::class, 'getKantinRiwayat']);
                Route::post('/transaksi', [SiswaKantinController::class, 'createProdukTransaksi']);
            });
        });

        Route::group(['prefix' => 'laundry'], function () {
            Route::group(['prefix' => 'layanan'], function () {
                Route::get('/', [SiswaLaundryController::class, 'getLaundryLayanan']);
                Route::get('/riwayat', [SiswaLaundryController::class, 'getLayananRiwayat']);
                Route::post('/transaksi', [SiswaLaundryController::class, 'createLayananTransaksi']);
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
            Route::get('/{produk}', [KantinProdukController::class, 'show']);
            Route::put('/{produk}', [KantinProdukController::class, 'update']);
            Route::delete('/{produk}', [KantinProdukController::class, 'destroy']);
        });

        //kategori crud
        Route::group(['prefix' => 'kategori'], function () {
            Route::get('/', [KantinProdukKategoriController::class, 'index']);
            Route::post('/', [KantinProdukKategoriController::class, 'create']);
            Route::get('/{kategori}', [KantinProdukKategoriController::class, 'show']);
            Route::put('/{kategori}', [KantinProdukKategoriController::class, 'update']);
            Route::delete('/{kategori}', [KantinProdukKategoriController::class, 'destroy']);
        });

        //transaksi
        Route::group(['prefix' => 'transaksi'], function () {
            Route::get('/', [KantinTransaksiController::class, 'getActiveTransaction']);
            Route::get('/riwayat', [KantinTransaksiController::class, 'getCompletedTransaction']);
            Route::get('/{transaksi}', [KantinTransaksiController::class, 'update']);
            Route::put('/{transaksi}/konfirmasi', [KantinTransaksiController::class, 'confirmInitialTransaction']);
        });

        //pengajuan
        Route::group(['prefix' => 'pengajuan'], function () {
            Route::post('/', [UsahaPengajuanController::class, 'create']);
            Route::get('/riwayat', [UsahaPengajuanController::class, 'index']);
        });
    });

    Route::group([
        'prefix' => 'laundry',
        'middleware' => 'checkrole:Laundry'
    ], function () {
        Route::group(['prefix' => 'layanan'], function () {
            Route::get('/', [LaundryLayananController::class, 'index']);
            Route::post('/', [LaundryLayananController::class, 'create']);
            Route::get('/{layanan}', [LaundryLayananController::class, 'show']);
            Route::put('/{layanan}', [LaundryLayananController::class, 'update']);
            Route::delete('/{layanan}', [LaundryLayananController::class, 'destroy']);
        });

        Route::group(['prefix' => 'transaksi'], function () {
            Route::get('/', [LaundryTransaksiController::class, 'getActiveTransaction']);
            Route::get('/riwayat', [LaundryTransaksiController::class, 'getCompletedTransaction']);
            Route::get('/{transaksi}', [LaundryTransaksiController::class, 'update']);
            Route::put('/{transaksi}/konfirmasi', [LaundryTransaksiController::class, 'confirmInitialTransaction']);
        });

        Route::group(['prefix' => 'pengajuan'], function () {
            Route::post('/', [UsahaPengajuanController::class, 'create']);
            Route::get('/riwayat', [UsahaPengajuanController::class, 'index']);
        });
    });

    Route::group([
        'prefix' => 'bendahara',
        'middleware' => 'checkrole:Bendahara'
    ], function () {
        Route::get('/laporan', [BendaharaController::class, 'getUsahaTransaksi']);

        Route::group(['prefix' => 'pengajuan'], function() {
                Route::get('/', [BendaharaPengajuanController::class, 'getUsahaPengajuan']);
                Route::put('/{pengajuan}', [BendaharaPengajuanController::class, 'confirmUsahaPengajuan']);
        });
    });

    Route::group([
        'prefix' => 'kepsek',
        'middleware' => 'checkrole:KepalaSekolah'
    ], function () {

        Route::get('/laporan', [KepsekController::class, 'getUsahaTransaksi']);

        Route::get('/pengajuan', [KepsekController::class, 'getUsahaPengajuan']);
        
    });
});

Route::post('/test', function (Request $request) {
    $siswa = Auth::user()->usaha->firstOrFail();
    return $siswa;
})->middleware('auth:api');