<?php

use App\Http\Controllers\BendaharaController;
use App\Http\Controllers\KantinPengajuanController;
use App\Http\Controllers\KantinProdukController;
use App\Http\Controllers\KantinProdukKategoriController;
use App\Http\Controllers\KantinTransaksiController;
use App\Http\Controllers\LaundryItemController;
use App\Http\Controllers\LaundryLayananController;
use App\Http\Controllers\LaundryPengajuanController;
use App\Http\Controllers\LaundryTransaksiKiloanController;
use App\Http\Controllers\LaundryTransaksiSatuanController;
use App\Http\Controllers\TopUpController;
use App\Models\KantinProduk;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\SiswaController;

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
        'prefix' => 'siswa',
        'middleware' => 'checkrole:Siswa'
    ], function() {

        Route::group(['prefix' => 'wallet'], function() {
            Route::get('/saldo'); //show saldo siswa
            Route::post('/riwayat'); //show riwayat saldo siswa
        });

        Route::group(['prefix' => 'kantin'], function() {
            Route::get('/produk'); //show all menu
            Route::get('/produk/{produk}/detail'); //show specific menu
            Route::post('/produk/{produk}/transaksi'); //create transaction
            Route::get('/riwayat'); //show riwayat kantin siswa
        });

        Route::group(['prefix' => 'laundry'], function() {
            Route::get('/satuan'); //show all satuan
            Route::get('/satuan/{satuan}/detail'); //show specific satuan
            Route::post('/satuan/{satuan}/transaksi'); //create transaction
            Route::get('/satuan/riwayat'); //show riwayat satuan siswa

            Route::get('/layanan'); //show all layanan
            Route::get('/layanan/{layanan}/detail'); //show specific layanan
            Route::post('/layanan/{layanan}/transaksi'); //create transaction
            Route::get('/layanan/riwayat'); //show riwayat layanan siswa
        });
    });

    Route::group([
        'prefix' => 'siswa',
        'middleware' => 'checkrole:Siswa'
    ], function() {

        Route::group(['prefix' => 'wallet'], function() {
            Route::get('/saldo'); //show saldo siswa
            Route::post('/riwayat'); //show riwayat saldo siswa
        });

        Route::group(['prefix' => 'kantin'], function() {
            Route::get('/produk'); //show all menu
            Route::get('/produk/{produk}/detail'); //show specific menu
            Route::post('/produk/{produk}/transaksi'); //create transaction
            Route::get('/riwayat'); //show riwayat kantin siswa
        });

        Route::group(['prefix' => 'laundry'], function() {
            Route::get('/satuan'); //show all satuan
            Route::get('/satuan/{satuan}/detail'); //show specific satuan
            Route::post('/satuan/{satuan}/transaksi'); //create transaction
            Route::get('/satuan/riwayat'); //show riwayat satuan siswa

            Route::get('/layanan'); //show all layanan
            Route::get('/layanan/{layanan}/detail'); //show specific layanan
            Route::post('/layanan/{layanan}/transaksi'); //create transaction
            Route::get('/layanan/riwayat'); //show riwayat layanan siswa
        });
    });

    Route::group([
        'prefix' => 'siswa',
        'middleware' => 'checkrole:Siswa'
    ], function () {

        Route::get('kantin/produk', [SiswaController::class, 'getKantinProduk']);
        Route::get('laundry/layanan', [SiswaController::class, 'getLaundryLayanan']);
        Route::get('laundry/item', [SiswaController::class, 'getLaundryitem']);

        Route::get('kantin/transaksi', [SiswaController::class, 'indexKantin']);
        Route::get('kantin/transaksi/{id}', [SiswaController::class, 'showKantin']);
        Route::post('kantin/transaksi', [SiswaController::class, 'createKantin']);


        Route::get('laundry/transaksi/kiloan', [SiswaController::class, 'indexLaundryKiloan']);
        Route::get('laundry/transaksi/kiloan/{id}', [SiswaController::class, 'showLaundryKiloan']);
        Route::post('laundry/transaksi/kiloan', [SiswaController::class, 'createLaundryKiloan']);

        Route::get('laundry/transaksi/satuan', [SiswaController::class, 'indexLaundrySatuan']);
        Route::get('laundry/transaksi/satuan/{id}', [SiswaController::class, 'showLaundrySatuan']);
        Route::post('laundry/transaksi/satuan', [SiswaController::class, 'createLaundrySatuan']);
    });

    Route::group([
        'prefix' => 'laundry',
        'middleware' => 'checkrole:Laundry'
    ], function () {
        Route::get('/item', [LaundryItemController::class, 'index']);
        Route::post('/item', [LaundryItemController::class, 'create']);
        Route::get('/item/{item}', [LaundryItemController::class, 'show']);
        Route::put('/item/{item}', [LaundryItemController::class, 'update']);
        Route::delete('/item/{item}', [LaundryItemController::class, 'destroy']);

        Route::get('/layanan', [LaundryLayananController::class, 'index']);
        Route::post('/layanan', [LaundryLayananController::class, 'create']);
        Route::get('/layanan/{layanan}', [LaundryLayananController::class, 'show']);
        Route::put('/layanan/{layanan}', [LaundryLayananController::class, 'update']);
        Route::delete('/layanan/{layanan}', [LaundryLayananController::class, 'destroy']);

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
        'middleware' => 'checkrole:Kantin,Siswa'
    ], function () {
        Route::get('/produk', [KantinProdukController::class, 'index']);
        Route::post('/produk', [KantinProdukController::class, 'create']);
        Route::put('/produk/{produk}', [KantinProdukController::class, 'update']);
        Route::delete('/produk/{produk}', [KantinProdukController::class, 'destroy']);

        Route::get('/kategori', [KantinProdukKategoriController::class, 'index']);
        Route::post('/kategori', [KantinProdukKategoriController::class, 'create']);
        Route::put('/kategori/{kategori}', [KantinProdukKategoriController::class, 'update']);
        Route::delete('/kategori/{kategori}', [KantinProdukKategoriController::class, 'destroy']);

        Route::get('/transaksi', [KantinTransaksiController::class, 'index']);
        Route::put('/transaksi/{transaksi}/konfirmasi', [KantinTransaksiController::class, 'confirmInitialTransaction']);
        Route::put('/transaksi/{transaksi}', [KantinTransaksiController::class, 'update']);

        Route::get('/pengajuan', [KantinPengajuanController::class, 'index']);
        Route::post('/pengajuan', [KantinPengajuanController::class, 'create']);
    });
});

Route::post('/test', function (Request $request) {
    $fields = $request->all();
    dd($fields['image']);
});
