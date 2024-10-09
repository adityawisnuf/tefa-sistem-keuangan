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

use App\Http\Controllers\ArusKasController;
use App\Http\Controllers\LabaRugiController;
use App\Http\Controllers\PrediksiPerencanaanKeuanganController;
use Illuminate\Http\Request;

use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\IndoRegionController;
use App\Http\Controllers\LaporanKeuanganController;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PendaftarController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\NeracaController;
use App\Http\Controllers\RasioKeuanganController;

use Illuminate\Support\Facades\Auth;

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
                Route::get('/', [SiswaKantinController::class, 'index']);
                Route::get('/transaksi', [SiswaKantinController::class, 'getKantinTransaksi']);
                Route::post('/transaksi/create', [SiswaKantinController::class, 'createProdukTransaksi']);
                Route::get('/{produk}', [SiswaKantinController::class, 'show']);
            });
        });

        Route::group(['prefix' => 'laundry'], function () {
            Route::group(['prefix' => 'layanan'], function () {
                Route::get('/', [SiswaLaundryController::class, 'index']);
                Route::get('/transaksi', [SiswaLaundryController::class, 'getLayananTransaksi']);
                Route::post('/transaksi/create', [SiswaLaundryController::class, 'createLayananTransaksi']);
                Route::get('/{layanan}', [SiswaLaundryController::class, 'show']);
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

Route::post('/test', function (Request $request) {
    $siswa = Auth::user()->usaha->firstOrFail();
    return $siswa;
})->middleware('auth:api'); 

Route::get('get-province', [IndoRegionController::class, 'getAllProvinces']);
Route::get('get-regency/{provinceId}', [IndoRegionController::class, 'getRegenciesByProvince']);
Route::get('get-district/{regencyId}', [IndoRegionController::class, 'getDistrictsByRegency']);
Route::get('get-village/{districtId}', [IndoRegionController::class, 'getVillagesByDistrict']);

Route::post('/validate-nik', [NIKController::class, 'validateNik']);
Route::post('/payment-callback', [PembayaranController::class, 'handleCallback']);
Route::get('test');

Route::group([
    'middleware' => ['auth:api', 'checkrole:KepalaSekolah,Bendahara']
], function () {
    Route::get('neraca', [NeracaController::class, 'index']);
    Route::get('laba-rugi', [LabaRugiController::class, 'index']);
    Route::get('arus-kas', [ArusKasController::class, 'index']);
    Route::get('rasio-keuangan', [RasioKeuanganController::class, 'index']);
    Route::get('rasio-keuangan-grafik', [RasioKeuanganController::class, 'getGraphicRatioByMonth']);
    Route::get('prediksi-perencanaan', [PrediksiPerencanaanKeuanganController::class, 'index']);
    Route::get('get-options-n', [NeracaController::class, 'getOptions']);
    Route::get('get-options-lr', [LabaRugiController::class, 'getOptions']);
    Route::get('get-options-ak', [ArusKasController::class, 'getOptions']);
    Route::get('get-options-rk', [RasioKeuanganController::class, 'getOptions']);
    Route::get('get-options-pp', [PrediksiPerencanaanKeuanganController::class, 'getOptions']);
});