<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PengeluaranExcelController;
use App\Http\Controllers\PrintExcelController;
use App\Models\Anggaran;
use App\Models\Kelas;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PembayaranSiswa;
use App\Models\Pengeluaran;
use App\Models\Siswa;
use App\Models\Pembayaran;

// Register and Login Routes
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::middleware(['auth:api'])->group(function () {

    Route::post('logout', [LogoutController::class, 'logout']);

    Route::get('/select/siswa', function () {
        $siswaData = Siswa::all();
        return response()->json([
            'message' => 'Berhasil mendapatkan data siswa',
            'success' => true,
            'data' => $siswaData->map(function ($siswa) {
                return [
                    'value' => $siswa->id,
                    'label' => $siswa->nama_depan . " " . $siswa->nama_belakang
                ];
            })
        ]);
    });


    Route::get('/select/kelas', function () {
        $kelasData = Kelas::all();
        return response()->json([
            'message' => 'Berhasil mendapatkan data kelas',
            'success' => true,
            'data' => $kelasData->map(function ($kelas) {
                return [
                    'value' => $kelas->id,
                    'label' => $kelas->kelas
                ];
            })
        ]);
    });
    
    Route::get('/select/anggaran', function () {
        $table = Anggaran::all();
        return response()->json([
            'message' => 'Berhasil mendapatkan data siswa',
            'success' => true,
            'data' => $table->map(function ($anggaran) {
                return [
                    'value' => $anggaran->id,
                    'label' => $anggaran->nama_anggaran
                ];
            })
        ]);
    });

    Route::middleware(['checkrole:Bendahara'])
        ->prefix('Bendahara')
        ->group(function () {
            Route::post('/pembayaran', [PembayaranController::class, 'index']); // Changed POST to GET
            Route::post('/pengeluaran', [PengeluaranController::class, 'index']); // Changed POST to GET
            Route::get('/export-pengeluaran', [PengeluaranExcelController::class, 'exportPengeluaran'])
                ->name('pengeluaran.exportExcel');
            Route::get('/pembayaran/export-excel', [PrintExcelController::class, 'exportExcel'])
                ->name('pembayaran.exportExcel');

            Route::get('/laporan/pembayaran', function () {
                $tgl_awal = request('tgl_awal');
                $tgl_akhir = request('tgl_akhir');

                if ($tgl_awal && $tgl_akhir) {
                    $pembayaran = PembayaranSiswa::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
                    $fileName = "Pembayaran_{$tgl_awal}-{$tgl_akhir}.pdf";
                } else {
                    $pembayaran = PembayaranSiswa::all();
                    $fileName = "Data_Keseluruhan_Pembayaran.pdf";
                }

                $data = ['pembayarans' => $pembayaran];
                $pdf = Pdf::loadView('print.pembayaran', $data);

                return $pdf->stream($fileName);
            })->name('laporan.pembayaran');

            Route::get('/laporan/pengeluaran', function () {
                $tgl_awal = request('tgl_awal');
                $tgl_akhir = request('tgl_akhir');

                if ($tgl_awal && $tgl_akhir) {
                    $semua_pengeluaran = Pengeluaran::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
                    $fileName = "Pengeluaran_{$tgl_awal}-{$tgl_akhir}.pdf";
                } else {
                    $semua_pengeluaran = Pengeluaran::all();
                    $fileName = "Data_Keseluruhan_Pengeluaran.pdf";
                }

                $data = ['pengeluarans' => $semua_pengeluaran];
                $pdf = Pdf::loadView('print.pengeluaran', $data);

                return $pdf->stream($fileName);
            })->name('laporan.pengeluaran');
        });

        Route::middleware(['checkrole:Kepala Sekolah'])
        ->prefix('Kepala Sekolah')
        ->group(function () {
            // Menggunakan metode GET untuk mengambil data pembayaran dan pengeluaran
            Route::post('/pembayaran', [PembayaranController::class, 'index']); 
            Route::post('/pengeluaran', [PengeluaranController::class, 'index']); 
    
            // Route untuk ekspor pengeluaran dan pembayaran
            Route::get('/export-pengeluaran', [PengeluaranExcelController::class, 'exportPengeluaran'])
                ->name('pengeluaran.exportExcel');
            Route::get('/pembayaran/export-excel', [PrintExcelController::class, 'exportExcel'])
                ->name('pembayaran.exportExcel');
    
            // Route untuk laporan pembayaran
            Route::get('/laporan/pembayaran', function () {
                $tgl_awal = request('tgl_awal');
                $tgl_akhir = request('tgl_akhir');
    
                if ($tgl_awal && $tgl_akhir) {
                    $pembayaran = PembayaranSiswa::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
                    $fileName = "Pembayaran_{$tgl_awal}-{$tgl_akhir}.pdf";
                } else {
                    $pembayaran = PembayaranSiswa::all();
                    $fileName = "Data_Keseluruhan_Pembayaran.pdf";
                }
    
                $data = ['pembayarans' => $pembayaran];
                $pdf = Pdf::loadView('print.pembayaran', $data);
    
                return $pdf->stream($fileName);
            })->name('laporan.pembayaran');
    
            // Route untuk laporan pengeluaran
            Route::get('/laporan/pengeluaran', function () {
                $tgl_awal = request('tgl_awal');
                $tgl_akhir = request('tgl_akhir');
    
                if ($tgl_awal && $tgl_akhir) {
                    $semua_pengeluaran = Pengeluaran::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
                    $fileName = "Pengeluaran_{$tgl_awal}-{$tgl_akhir}.pdf";
                } else {
                    $semua_pengeluaran = Pengeluaran::all();
                    $fileName = "Data_Keseluruhan_Pengeluaran.pdf";
                }
    
                $data = ['pengeluarans' => $semua_pengeluaran];
                $pdf = Pdf::loadView('print.pengeluaran', $data);
    
                return $pdf->stream($fileName);
            })->name('laporan.pengeluaran');
        });    

});

