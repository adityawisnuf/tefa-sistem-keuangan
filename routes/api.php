<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\PengeluaranController;
use App\Http\Controllers\Api\PengeluaranExcelController;
use App\Http\Controllers\Api\PrintExcelController;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Anggaran;
use App\Models\Kelas;
use App\Models\PembayaranSiswa;
use App\Models\Pengeluaran;
use App\Models\Siswa;
use App\Models\Pembayaran;
use App\Models\Sekolah;
use Illuminate\Support\Facades\Log;

// Register and Login Routes
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('logout', [LogoutController::class, 'logout']);

    // Route untuk mendapatkan data siswa
    Route::get('/select/siswa', function () {
        Log::info('Route select/siswa accessed'); // Tambahkan log
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

    // Route untuk mendapatkan data anggaran
    Route::get('/select/anggaran', function () {
        $table = Anggaran::all();
        return response()->json([
            'message' => 'Berhasil mendapatkan data anggaran',
            'success' => true,
            'data' => $table->map(function ($anggaran) {
                return [
                    'value' => $anggaran->id,
                    'label' => $anggaran->nama_anggaran
                ];
            })
        ]);
    });

    // Routes untuk role "Bendahara"
    Route::middleware(['checkrole:Bendahara'])->prefix('Bendahara')->group(function () {
        Route::post('/pemasukan', [PembayaranController::class, 'index']); // Route untuk pemasukan
        Route::post('/pengeluaran', [PengeluaranController::class, 'index']);
        Route::get('/export-pengeluaran', [PengeluaranExcelController::class, 'exportPengeluaran'])
            ->name('pengeluaran.exportExcel');
        Route::get('/pembayaran/export-excel', [PrintExcelController::class, 'exportExcel'])
            ->name('pembayaran.exportExcel');

        // Laporan pembayaran PDF
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

            $data = ['pembayarans' => $pembayaran, 'sekolah' => Sekolah::first()];
            $pdf = Pdf::loadView('print.pembayaran', $data);

            return $pdf->stream($fileName);
        })->name('laporan.pembayaran');

        // Laporan pengeluaran PDF
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

            $data = ['pengeluarans' => $semua_pengeluaran, 'sekolah' => Sekolah::first()];
            $pdf = Pdf::loadView('print.pengeluaran', $data);

            return $pdf->stream($fileName);
        })->name('laporan.pengeluaran');
    });

    // Routes untuk role "Kepala Sekolah"
    Route::middleware(['checkrole:Kepala Sekolah'])->prefix('Kepala Sekolah')->group(function () {
        Route::post('/pemasukan', [PembayaranController::class, 'index']); // Route untuk pemasukan
        Route::post('/pengeluaran', [PengeluaranController::class, 'index']); // Route untuk pengeluaran
        Route::get('/export-pengeluaran', [PengeluaranExcelController::class, 'exportPengeluaran'])
            ->name('pengeluaran.exportExcel');
        Route::get('/pembayaran/export-excel', [PrintExcelController::class, 'exportExcel'])
            ->name('pembayaran.exportExcel');

        // Laporan pembayaran PDF
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

            $data = ['pembayarans' => $pembayaran, 'sekolah' => Sekolah::first()];
            $pdf = Pdf::loadView('print.pembayaran', $data);

            return $pdf->stream($fileName);
        })->name('laporan.pembayaran');

        // Laporan pengeluaran PDF
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

            $data = ['pengeluarans' => $semua_pengeluaran, 'sekolah' => Sekolah::first()];
            $pdf = Pdf::loadView('print.pengeluaran', $data);

            return $pdf->stream($fileName);
        })->name('laporan.pengeluaran');
    });
});
