<?php

use App\Http\Controllers\KantinPengajuanController;
use App\Http\Controllers\KantinProdukController;
use App\Http\Controllers\KantinProdukKategoriController;
use App\Http\Controllers\KantinTransaksiController;
use App\Http\Controllers\LaundryItemController;
use App\Http\Controllers\TopUpController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;

// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::post('duitku/callback', [TopUpController::class, 'callback']);

Route::group([
    'middleware' => ['auth:api']
], function () {
    Route::post('logout', [LogoutController::class, 'logout']);

    Route::group([
        'prefix' => 'duitku'
    ], function() {
        Route::get('get-payment-method', [TopUpController::class, 'getPaymentMethod']);
        Route::post('request-transaksi', [TopUpController::class, 'requestTransaction']);
    });

    Route::group([
        'prefix' => 'laundry',
        'middleware' => 'checkrole:Laundry'
    ], function() {
        Route::group(['prefix' => 'item'], function() {
            Route::get('/', [LaundryItemController::class, 'index']);
            Route::post('/', [LaundryItemController::class, 'create']);
            Route::put('/{item}', [LaundryItemController::class, 'update']);
            Route::delete('/{item}', [LaundryItemController::class, 'destroy']);
        });
    });

    Route::group([
        'prefix' => 'transaksi',
        'middleware' => 'checkrole:Siswa'
    ], function() {
        Route::group(['prefix' => 'kantin'], function() {
            Route::get('/', [KantinTransaksiController::class, 'index']);
            Route::post('/', [KantinTransaksiController::class, 'create']);
        });
    });

    Route::group([
        'prefix' => 'kantin',
        'middleware' => 'checkrole:Kantin'
    ], function() {
        Route::group(['prefix' => 'produk'], function() {
            Route::get('/', [KantinProdukController::class, 'index']);
            Route::post('/', [KantinProdukController::class, 'create']);
            Route::put('/{produk}', [KantinProdukController::class, 'update']);
            Route::delete('/{produk}', [KantinProdukController::class, 'destroy']);
        });
        Route::group(['prefix' => 'kategori'], function() {
            Route::get('/', [KantinProdukKategoriController::class, 'index']);
            Route::post('/', [KantinProdukKategoriController::class, 'create']);
            Route::put('/{kategori}', [KantinProdukKategoriController::class, 'update']);
            Route::delete('/{kategori}', [KantinProdukKategoriController::class, 'destroy']);
        });
        Route::group(['prefix' => 'pengajuan'], function() {
            Route::get('/', [KantinPengajuanController::class, 'index']);
            Route::post('/', [KantinPengajuanController::class, 'create']);
        });
    });
});