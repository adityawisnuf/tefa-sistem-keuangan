<?php

use App\Http\Controllers\Api\AsetSekolahController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Models\AsetSekolah;

// ROLE : Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::group([
    'middleware' => ['auth:api'],
], function () {
    Route::post('logout', [LogoutController::class, 'logout']);
});

// ROLE: Admin
Route::group([
    'middleware' => ['auth:api', 'checkrole:Admin'],
    'prefix' => "Admin"
], function () {
    // CRUD
    Route::post('/aset', [AsetSekolahController::class, 'store']);
    Route::get('/aset', [AsetSekolahController::class, 'index']);
    Route::patch('/aset/{aset}/', [AsetSekolahController::class, 'update']);
    Route::delete('/aset/{aset}', [AsetSekolahController::class, 'destroy']);

    Route::get('/laporan/inventaris', function () {
        $tgl_awal = request('tgl_awal');
        $tgl_akhir = request('tgl_akhir');

        if ($tgl_awal && $tgl_akhir) {
            // $asset = AsetSekolah::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
            $fileName = "Aset {$tgl_awal} - {$tgl_akhir}.pdf";
        } else {
            $asset = AsetSekolah::all();
            $fileName = "Data Keseluruhan Asset.pdf";
        }

        $data = ['assets' => $asset];
        $pdf = Pdf::loadView('print.inventaris', $data);

        return $pdf->stream($fileName);
    })->name('laporan.inventaris');
});

// ROLE: Bendahara
Route::group([
    'middleware' => ['auth:api', 'checkrole:Bendahara'],
    'prefix' => 'Bendahara'
], function () {

    Route::get('/laporan/inventaris', function () {
        $tgl_awal = request('tgl_awal');
        $tgl_akhir = request('tgl_akhir');

        if ($tgl_awal && $tgl_akhir) {
            $asset = AsetSekolah::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
            $fileName = "Aset {$tgl_awal} - {$tgl_akhir}.pdf";
        } else {
            $asset = AsetSekolah::all();
            $fileName = "Data Keseluruhan Asset.pdf";
        }

        $data = ['assets' => $asset];
        $pdf = Pdf::loadView('print.inventaris', $data);

        return $pdf->stream($fileName);
    })->name('laporan.inventaris');
    Route::post('/aset', [AsetSekolahController::class, 'create']);
    Route::get('/aset', [AsetSekolahController::class, 'index']);
    Route::patch('/aset/{aset}/', [AsetSekolahController::class, 'update']);
});

// Role Kepala Sekolah
Route::group([
    'middleware' => ['auth:api', 'checkrole:Kepala Sekolah'],
    'prefix' => 'Kepala Sekolah'
], function () {

    Route::get('/aset', [AsetSekolahController::class, 'index']);

    Route::get('/laporan/inventaris', function () {
        $tgl_awal = request('tgl_awal');
        $tgl_akhir = request('tgl_akhir');

        if ($tgl_awal && $tgl_akhir) {
            $asset = AsetSekolah::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
            $fileName = "Aset {$tgl_awal} - {$tgl_akhir}.pdf";
        } else {
            $asset = AsetSekolah::all();
            $fileName = "Data Keseluruhan Asset.pdf";
        }

        $data = ['assets' => $asset];
        $pdf = Pdf::loadView('print.inventaris', $data);

        return $pdf->stream($fileName);
    })->name('laporan.inventaris');
});
