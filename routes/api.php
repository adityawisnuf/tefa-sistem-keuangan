<?php

use App\Http\Controllers\BendaharaController;
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


        Route::get('/laundry/kiloan', [LaundryTransaksiKiloanController::class, 'index']);
        Route::post('/laundry/kiloan', [LaundryTransaksiKiloanController::class, 'create']);

        Route::get('/laundry/satuan', [LaundryTransaksiSatuanController::class, 'index']);
        Route::post('/laundry/satuan', [LaundryTransaksiSatuanController::class, 'create']);
    });

    Route::group([
        'prefix' => 'laundry',
        'middleware' => 'checkrole:Laundry'
    ], function () {
        Route::get('/item', [LaundryItemController::class, 'index']);
        Route::post('/item', [LaundryItemController::class, 'create']);
        Route::put('/item/{item}', [LaundryItemController::class, 'update']);
        Route::delete('/item/{item}', [LaundryItemController::class, 'destroy']);

        Route::put('/laundry/kiloan/{transaksi}/konfirmasi', [LaundryTransaksiKiloanController::class, 'confirmInitialTransaction']);
        Route::put('/laundry/kiloan/{transaksi}', [LaundryTransaksiKiloanController::class, 'update']);

        Route::put('/laundry/satuan/{transaksi}/konfirmasi', [LaundryTransaksiSatuanController::class, 'confirmInitialTransaction']);
        Route::put('/laundry/satuan/{transaksi}', [LaundryTransaksiSatuanController::class, 'update']);

        Route::get('/pengajuan', [LaundryPengajuanController::class, 'index']);
        Route::post('/pengajuan', [LaundryPengajuanController::class, 'create']);
    });
    Route::group([
        'prefix' => 'bendahara',
        'middleware' => 'checkrole:Bendahara'
    ], function () {
        Route::get('/laporan-penjualan', [BendaharaController::class, 'index']);

        Route::get('/laporan-penjualan/kantin', [BendaharaController::class, 'getKantinTransaksi']);
        Route::get('/laporan-penjualan/laundry-satuan', [BendaharaController::class, 'getLaundryTransaksiSatuan']);
        Route::get('/laporan-penjualan/laundry-kiloan', [BendaharaController::class, 'getLaundryTransaksiKiloan']);

        Route::get('/laporan-pengajuan/kantin', [BendaharaController::class, 'getKantinPengajuan']);
        Route::put('/laporan-pengajuan/kantin/{pengajuan}', [KantinPengajuanController::class, 'update']);

        Route::get('/laporan-pengajuan/laundry', [BendaharaController::class, 'getLaundryPengajuan']);
        Route::put('/laporan-pengajuan/laundry/{pengajuan}', [LaundryPengajuanController::class, 'update']);

    });


    Route::group([
        'prefix' => 'kantin',
        'middleware' => 'checkrole:Kantin'
    ], function () {
        Route::get('/', [KantinProdukController::class, 'index']);
        Route::post('/produk', [KantinProdukController::class, 'create']);
        Route::put('/produk/{produk}', [KantinProdukController::class, 'update']);
        Route::delete('/produk/{produk}', [KantinProdukController::class, 'destroy']);

        Route::get('/kategori', [KantinProdukKategoriController::class, 'index']);
        Route::post('/kategori', [KantinProdukKategoriController::class, 'create']);
        Route::put('/kategori/{kategori}', [KantinProdukKategoriController::class, 'update']);
        Route::delete('/kategori/{kategori}', [KantinProdukKategoriController::class, 'destroy']);

        Route::put('/kantin/{transaksi}/konfirmasi', [KantinTransaksiController::class, 'confirmInitialTransaction']);
        Route::put('/kantin/{transaksi}', [KantinTransaksiController::class, 'update']);

        Route::get('/pengajuan', [KantinPengajuanController::class, 'index']);
        Route::post('/pengajuan', [KantinPengajuanController::class, 'create']);
    });
});

Route::get('/test', function () {
    return Auth::user()->laundry->first()->id;
})
    ->middleware('auth:api');
