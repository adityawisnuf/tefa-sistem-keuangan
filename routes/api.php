<?php

use App\Http\Controllers\KantinPengajuanController;
use App\Http\Controllers\KantinProdukController;
use App\Http\Controllers\KantinProdukKategoriController;
use App\Http\Controllers\KantinTransaksiController;
use App\Http\Controllers\LaundryItemController;
use App\Http\Controllers\LaundryPengajuanController;
use App\Http\Controllers\LaundryTransaksiKiloanController;
use App\Http\Controllers\LaundryTransaksiSatuanController;
use App\Http\Controllers\TopUpController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;

// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::post('/duitku/callback', [TopUpController::class, 'callback']);

Route::group([
    'middleware' => ['auth:api']
], function () {
    Route::post('logout', [LogoutController::class, 'logout']);

    Route::get('/duitku/get-payment-method', [TopUpController::class, 'getPaymentMethod']);
    Route::post('/duitku/request-transaksi', [TopUpController::class, 'requestTransaction']);

    Route::group([
        'prefix' => 'transaksi',
        'middleware' => 'checkrole:Siswa'
    ], function () {
        Route::get('/kantin', [KantinTransaksiController::class, 'index']);
        Route::post('/kantin', [KantinTransaksiController::class, 'create']);
        Route::post('/kantin/{transaksi}/konfirmasi', [KantinTransaksiController::class, 'confirmInitialTransaction']);
        Route::post('/kantin/{transaksi}', [KantinTransaksiController::class, 'update']);

        Route::get('/laundry/kiloan', [LaundryTransaksiKiloanController::class, 'index']);
        Route::post('/laundry/kiloan', [LaundryTransaksiKiloanController::class, 'create']);
        Route::post('/laundry/kiloan/{transaksi}/konfirmasi', [LaundryTransaksiKiloanController::class, 'confirmInitialTransaction']);
        Route::post('/laundry/kiloan/{transaksi}', [LaundryTransaksiKiloanController::class, 'update']);

        Route::get('/laundry/satuan', [LaundryTransaksiSatuanController::class, 'index']);
        Route::post('/laundry/satuan', [LaundryTransaksiSatuanController::class, 'create']);
        Route::post('/laundry/satuan/{transaksi}/konfirmasi', [LaundryTransaksiSatuanController::class, 'confirmInitialTransaction']);
        Route::post('/laundry/satuan/{transaksi}', [LaundryTransaksiSatuanController::class, 'update']);
    });

    Route::group([
        'prefix' => 'laundry',
        'middleware' => 'checkrole:Laundry'
    ], function () {
        Route::get('/item', [LaundryItemController::class, 'index']);
        Route::post('/item', [LaundryItemController::class, 'create']);
        Route::put('/item/{item}', [LaundryItemController::class, 'update']);
        Route::delete('/item/{item}', [LaundryItemController::class, 'destroy']);

        Route::get('/pengajuan', [LaundryPengajuanController::class, 'index']);
        Route::post('/pengajuan', [LaundryPengajuanController::class, 'create']);
    });


    Route::group([
        'prefix' => 'kantin',
        'middleware' => 'checkrole:Kantin'
    ], function () {
        Route::get('/produk', [KantinProdukController::class, 'index']);
        Route::post('/produk', [KantinProdukController::class, 'create']);
        Route::put('/produk/{produk}', [KantinProdukController::class, 'update']);
        Route::delete('/produk/{produk}', [KantinProdukController::class, 'destroy']);

        Route::get('/kategori', [KantinProdukKategoriController::class, 'index']);
        Route::post('/kategori', [KantinProdukKategoriController::class, 'create']);
        Route::put('/kategori/{kategori}', [KantinProdukKategoriController::class, 'update']);
        Route::delete('/kategori/{kategori}', [KantinProdukKategoriController::class, 'destroy']);

        Route::get('/pengajuan', [KantinPengajuanController::class, 'index']);
        Route::post('/pengajuan', [KantinPengajuanController::class, 'create']);
    });
});

Route::get('/test', function () {
    return Auth::user()->laundry->first()->id;
})
    ->middleware('auth:api');