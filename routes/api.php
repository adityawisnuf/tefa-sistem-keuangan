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
use App\Http\Controllers\SiswaKantinController;
use App\Http\Controllers\SiswaWalletController;
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
            Route::get('/saldo', [SiswaWalletController::class, 'getSaldo']); //show saldo siswa
            Route::get('/riwayat', [SiswaWalletController::class, 'getRiwayat']); //show riwayat saldo siswa
        });

        Route::group(['prefix' => 'kantin'], function() {
            Route::group(['prefix' => 'produk'], function() {
                Route::get('/', [SiswaKantinController::class, 'getProduk']); //show all menu
                Route::get('/riwayat', [SiswaKantinController::class, 'getKantinRiwayat']); //show riwayat kantin siswa
                Route::get('/{produk}', [SiswaKantinController::class, 'getProdukDetail']); //show specific menu
                Route::post('/{produk}/transaksi', [SiswaKantinController::class, 'createProdukTransaksi']); //create transaction
            });
        });
        
        Route::group(['prefix' => 'laundry'], function() {
            Route::get('/satuan'); //show all satuan
            Route::get('/satuan/{satuan}'); //show specific satuan  
            Route::post('/satuan/{satuan}/transaksi'); //create transaction
            Route::get('/satuan/riwayat'); //show riwayat satuan siswa
            
            Route::get('/layanan'); //show all layanan
            Route::get('/layanan/{layanan}'); //show specific layanan
            Route::post('/layanan/{layanan}/transaksi'); //create transaction
            Route::get('/layanan/riwayat'); //show riwayat layanan siswa
        });
    });
    
    Route::group([
        'prefix' => 'kantin',
        'middleware' => 'checkrole:Kantin'
    ], function () {
        //produk crud
        Route::group(['prefix' => 'produk'], function() {
            Route::get('/', [KantinProdukController::class, 'index']);
            Route::post('/', [KantinProdukController::class, 'create']);
            Route::get('/{produk}', [KantinProdukController::class, 'show']);
            Route::put('/{produk}', [KantinProdukController::class, 'update']);
            Route::delete('/{produk}', [KantinProdukController::class, 'destroy']);
        });
        
        //kategori crud
        Route::group(['prefix' => 'kategori'], function() {
            Route::get('/', [KantinProdukKategoriController::class, 'index']);
            Route::post('/', [KantinProdukKategoriController::class, 'create']);
            Route::get('/{kategori}', [KantinProdukKategoriController::class, 'show']);
            Route::put('/{kategori}', [KantinProdukKategoriController::class, 'update']);
            Route::delete('/{kategori}', [KantinProdukKategoriController::class, 'destroy']);
        });
        
        //transaksi
        Route::group(['prefix' => 'transaksi'], function() {
            Route::get('/', [KantinTransaksiController::class, 'index']);
            Route::put('/{transaksi}/konfirmasi', [KantinTransaksiController::class, 'confirmInitialTransaction']);
            Route::put('/{transaksi}', [KantinTransaksiController::class, 'update']);
            Route::get('/riwayat');
        });

        //pengajuan
        Route::group(['prefix' => 'pengajuan'], function() {
            Route::post('/', [KantinPengajuanController::class, 'create']);
            Route::get('/riwayat', [KantinPengajuanController::class, 'index']);
        });
    });

    Route::group([
        'prefix' => 'laundry',
        'middleware' => 'checkrole:Laundry'
    ], function () {
        //item crud
        Route::group(['prefix' => 'item'], function() {
            Route::get('/', [LaundryItemController::class, 'index']);
            Route::post('/', [LaundryItemController::class, 'create']);
            Route::get('/{item}', [LaundryItemController::class, 'show']);
            Route::put('/{item}', [LaundryItemController::class, 'update']);
            Route::delete('/{item}', [LaundryItemController::class, 'destroy']);
        });

        Route::group(['prefix' => 'layanan'], function() {
            Route::get('/', [LaundryLayananController::class, 'index']);
            Route::post('/', [LaundryLayananController::class, 'create']);
            Route::get('/{layanan}', [LaundryLayananController::class, 'show']);
            Route::put('/{layanan}', [LaundryLayananController::class, 'update']);
            Route::delete('/{layanan}', [LaundryLayananController::class, 'destroy']);
        });

        Route::group(['prefix' => 'transaksi'], function() {
            Route::group(['prefix' => 'satuan'], function() {
                Route::get('/');
                Route::put('/{transaksi}/konfirmasi');
                Route::put('/{transaksi}');
            });

            Route::group(['prefix' => 'kiloan'], function() {
                Route::get('/');
                Route::put('/{transaksi}/konfirmasi');
                Route::put('/{transaksi}');
            });
        });
        
        Route::group(['prefix' => 'pengajuan'], function() {
            Route::post('/', [LaundryPengajuanController::class, 'create']);
            Route::get('/riwayat', [LaundryPengajuanController::class, 'index']);
        });
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
});

Route::post('/test', function (Request $request) {
    $fields = $request->all();
    dd($fields['image']);
});