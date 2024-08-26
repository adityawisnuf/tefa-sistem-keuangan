<?php

use App\Http\Controllers\Api\AnggaranController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Models\Anggaran;

// ROLE: Admin, Kepala Sekolah, Bendahara

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::group([
    'middleware' => ['auth:api'],
], function () {
    Route::post('logout', [LogoutController::class, 'logout']);

    // Role: Admin
    Route::group([
        'middleware' => ['checkrole:Admin'],
        'prefix' => "Admin"
    ], function () {
        Route::post('/anggaran', [AnggaranController::class, 'store']);
        Route::get('/anggaran', [AnggaranController::class, 'index']);
        Route::patch('/anggaran/{anggaran}/', [AnggaranController::class, 'update']);
        Route::delete('/anggaran/{anggaran}', [AnggaranController::class, 'destroy']);
        Route::get('/anggaran-data', [AnggaranController::class, 'getAnggaranData']);

        Route::get('/laporan/anggaran', function () {
            $tgl_awal = request('tgl_awal');
            $tgl_akhir = request('tgl_akhir');

            if ($tgl_awal && $tgl_akhir) {
                $anggaran = Anggaran::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
                $fileName = "Aset {$tgl_awal} - {$tgl_akhir}.pdf";
            } else {
                $anggaran = Anggaran::all();
                $fileName = "Data Keseluruhan Anggaran.pdf";
            }

            $data = ['anggarans' => $anggaran];
            $pdf = Pdf::loadView('print.anggaran', $data);

            return $pdf->stream($fileName);
        })->name('laporan.anggaran');
        Route::get('monitoring/get', [AnggaranController::class, 'getAnggaranCount']);
    });

    // Role: Bendahara
    Route::group([
        'middleware' => ['checkrole:Bendahara'],
        'prefix' => 'Bendahara'
    ], function () {
        Route::post('/anggaran', [AnggaranController::class, 'store']);
        Route::get('/anggaran', [AnggaranController::class, 'index']);
        Route::patch('/anggaran/{anggaran}/', [AnggaranController::class, 'update']);

        Route::get('/laporan/anggaran', function () {
            $tgl_awal = request('tgl_awal');
            $tgl_akhir = request('tgl_akhir');

            if ($tgl_awal && $tgl_akhir) {
                $anggaran = Anggaran::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
                $fileName = "Anggaran {$tgl_awal} - {$tgl_akhir}.pdf";
            } else {
                $anggaran = Anggaran::all();
                $fileName = "Data Keseluruhan Anggaran.pdf";
            }

            $data = ['anggarans' => $anggaran];
            $pdf = Pdf::loadView('print.anggaran', $data);

            return $pdf->stream($fileName);
        })->name('laporan.anggaran');
    });

    // Role: Kepala Sekolah
    Route::group([
        'middleware' => ['checkrole:Kepala Sekolah'],
        'prefix' => 'Kepala Sekolah'
    ], function () {
        Route::get('/anggaran', [AnggaranController::class, 'index']);
        Route::patch('/anggaran/{anggaran}/', [AnggaranController::class, 'update']);
        Route::get('/anggaran/chart-data', [AnggaranController::class, 'getChartData']);

        Route::get('/laporan/anggaran', function () {
            $tgl_awal = request('tgl_awal');
            $tgl_akhir = request('tgl_akhir');

            if ($tgl_awal && $tgl_akhir) {
                $anggaran = Anggaran::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
                $fileName = "Anggaran {$tgl_awal} - {$tgl_akhir}.pdf";
            } else {
                $anggaran = Anggaran::all();
                $fileName = "Data Keseluruhan Anggaran.pdf";
            }

            $data = ['anggarans' => $anggaran];
            $pdf = Pdf::loadView('print.anggaran', $data);

            return $pdf->stream($fileName);
        })->name('laporan.anggaran');
    });
});
