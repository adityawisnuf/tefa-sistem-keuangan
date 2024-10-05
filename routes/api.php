    <?php

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
            Route::get('/wallet/{siswa}', [OrangTuaController::class, 'getRiwayatWalletSiswa']);
            Route::get('/kantin/transaksi/{id}', [OrangTuaController::class, 'getRiwayatKantinSiswa']);
            Route::get('/laundry/transaksi/{id}', [OrangTuaController::class, 'getRiwayatLaundrySiswa']);
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
                Route::get('/transaksi', [SiswaKantinController::class, 'getKantinTransaksi']);
                Route::post('/transaksi/create', [SiswaKantinController::class, 'createProdukTransaksi']);
                Route::get('/{produk}', [SiswaKantinController::class, 'getProdukDetail']);
            });
        });

        Route::group(['prefix' => 'laundry'], function () {
            Route::group(['prefix' => 'layanan'], function () {
                Route::get('/', [SiswaLaundryController::class, 'getLayanan']);
                Route::get('/transaksi', [SiswaLaundryController::class, 'getLayananTransaksi']);
                Route::post('/transaksi/create', [SiswaLaundryController::class, 'createLayananTransaksi']);
                Route::get('/{layanan}', [SiswaLaundryController::class, 'getLayananDetail']);
            });
        });
    });

    Route::group([
        'prefix' => 'kantin',
        'middleware' => 'checkrole:Kantin'
    ], function () {
        Route::group(['prefix' => 'produk'], function () {
            Route::get('/', [KantinProdukController::class, 'index']);
            Route::post('/', [KantinProdukController::class, 'create']);
            Route::get('/{produk}', [KantinProdukController::class, 'show']);
            Route::put('/{produk}', [KantinProdukController::class, 'update']);
            Route::delete('/{produk}', [KantinProdukController::class, 'destroy']);
        });

        Route::group(['prefix' => 'kategori'], function () {
            Route::get('/', [KantinProdukKategoriController::class, 'index']);
            Route::post('/', [KantinProdukKategoriController::class, 'create']);
            Route::get('/{kategori}', [KantinProdukKategoriController::class, 'show']);
            Route::put('/{kategori}', [KantinProdukKategoriController::class, 'update']);
            Route::delete('/{kategori}', [KantinProdukKategoriController::class, 'destroy']);
        });

        Route::group(['prefix' => 'transaksi'], function () {
            Route::get('/', [KantinTransaksiController::class, 'index']);
            Route::post('/{transaksi}', [KantinTransaksiController::class, 'update']);
            Route::post('/{transaksi}/konfirmasi', [KantinTransaksiController::class, 'confirm']);
        });
        
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
            Route::get('/{layanan}', [LaundryLayananController::class, 'show']);
            Route::put('/{layanan}', [LaundryLayananController::class, 'update']);
            Route::delete('/{layanan}', [LaundryLayananController::class, 'destroy']);
        });

        Route::group(['prefix' => 'transaksi'], function () {
            Route::get('/', [LaundryTransaksiController::class, 'index']);
            Route::get('/{transaksi}', [LaundryTransaksiController::class, 'show']);
            Route::post('/{transaksi}', [LaundryTransaksiController::class, 'update']);
            Route::post('/{transaksi}/konfirmasi', [LaundryTransaksiController::class, 'confirm']);
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
        Route::get('/kantin/laporan', [BendaharaLaporanController::class, 'getKantinTransaksi']);
        Route::get('/kantin/laporan/{id}', [BendaharaLaporanController::class, 'getDetailKantinTransaksi']);

        Route::get('/laundry/laporan', [BendaharaLaporanController::class, 'getLaundryTransaksi']);
        Route::get('/laundry/laporan/{id}', [BendaharaLaporanController::class, 'getDetailLaundryTransaksi']);

        Route::get('/pengajuan', [BendaharaPengajuanController::class, 'getUsahaPengajuan']);
        Route::put('/pengajuan/{pengajuan}', [BendaharaPengajuanController::class, 'confirmUsahaPengajuan']);
    });

    Route::group([
        'prefix' => 'kepsek',
        'middleware' => 'checkrole:KepalaSekolah'
    ], function () {
        Route::get('/kantin/laporan', [KepsekLaporanController::class, 'getKantinTransaksi']);
        Route::get('/kantin/laporan/{transaksi}', [KepsekLaporanController::class, 'getDetailKantinTransaksi']);

        Route::get('/laundry/laporan', [KepsekLaporanController::class, 'getLaundryTransaksi']);
        Route::get('/laundry/laporan/{transaksi}', [KepsekLaporanController::class, 'getDetailLaundryTransaksi']);

        Route::get('/pengajuan', [KepsekPengajuanController::class, 'getUsahaPengajuan']);
    });
});

Route::post('/test', function (Request $request) {
    $siswa = Auth::user()->usaha->firstOrFail();
    return $siswa;
})->middleware('auth:api'); 