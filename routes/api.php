<?php

use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\PembayaranKategoriController;
use App\Http\Controllers\Api\PembayaranSiswaController;
use App\Http\Controllers\Api\SiswaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PembayaranManualController;
use App\Http\Controllers\RegisterController;
use App\Models\Kelas;
use App\Models\Pembayaran;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::post('duitku/callback', [PembayaranController::class, 'duitkuCallbackHandler'])->name('payment.transaction.callback');

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [LogoutController::class, 'logout']);

    Route::prefix('select')->group(function () {
        Route::get('/siswa', function () {
            $siswaData = Siswa::all();

            return response()->json([
                'message' => 'Berhasil mendapatkan data siswa',
                'success' => true,
                'data' => $siswaData->map(function ($siswa) {
                    return [
                        'value' => $siswa->id,
                        'label' => $siswa->nama_depan.' '.$siswa->nama_belakang,
                    ];
                }),
            ]);
        });

        Route::get('/kelas', function () {
            $kelasData = Kelas::all();

            return response()->json([
                'message' => 'Berhasil mendapatkan data kelas',
                'success' => true,
                'data' => $kelasData->map(function ($kelas) {
                    return [
                        'value' => $kelas->id,
                        'label' => $kelas->kelas,
                    ];
                }),
            ]);
        });

        Route::get('/jurusan', function () {
            Log::info('Route select/jurusan accessed');
            $jurusanData = Kelas::all();

            return response()->json([
                'message' => 'Berhasil mendapatkan data jurusan',
                'success' => true,
                'data' => $jurusanData->map(function ($kelas) {
                    return [
                        'value' => $kelas->id,
                        'label' => $kelas->jurusan,
                    ];
                }),
            ]);
        });
    });
    Route::prefix('payment')->group(function () {
        Route::get('/me', [PembayaranController::class, 'getCurrent'])->name('payment.transaction.getMonth');
        Route::get('/me/yearly', [PembayaranController::class, 'getCurrentYear'])->name('payment.transaction.getYear');
        Route::get('/yearly', [PembayaranController::class, 'getRiwayatTahunan'])->name('payment.transaction.getYear');
        Route::get('/', [PembayaranController::class, 'getRiwayat'])->name('payment.transaction.request');
        Route::get('methods/{id}', [PembayaranController::class, 'getPaymentMethod'])->name('payment.methods');
        Route::post('transaction/request', [PembayaranController::class, 'requestTransaksi'])->name('payment.transaction.request');
        Route::post('installment/request', [PembayaranController::class, 'requestTransaksiCicilan'])->name('payment.transaction.request');
        Route::post('cancel/{merchant_order_id}', [PembayaranController::class, 'batalTransaksi'])->name('payment.transaction.request');
        Route::post('assign', function (Request $request) {
            $data = $request->validate([
                'pembayaran_kategori_id' => 'required|exists:pembayaran_kategori,id',
                'siswa_id' => 'nullable',
                'kelas_id' => 'nullable|exists:kelas,id',
                'nominal' => 'required|numeric',
            ]);

            // Start building the query
            $query = Pembayaran::where('pembayaran_kategori_id', $data['pembayaran_kategori_id'])
                ->where('nominal', $data['nominal']);

            // Add siswa_id to the query if it is present
            if (! empty($data['siswa_id'])) {
                $query->where('siswa_id', $data['siswa_id']);
            }

            // Add kelas_id to the query if it is present
            if (! empty($data['kelas_id'])) {
                $query->where('kelas_id', $data['kelas_id']);
            }

            // Check if a matching Pembayaran already exists
            $existingPembayaran = $query->first();

            if ($existingPembayaran) {
                return response()->json(['success' => false, 'message' => 'Pembayaran with the same specifications already exists'], 400);
            }

            // If no existing Pembayaran, create a new one
            $data['status'] = true;
            $pembayaran = Pembayaran::create($data);

            return response()->json(['success' => true, 'message' => 'Berhasil membuat pembayaran baru', 'data' => $pembayaran]);
        })->name('payment.assign');
    });
    //Role: ADMIN
    Route::middleware('checkrole:Admin')->prefix('Admin')->group(function () {
        Route::get('students', [PembayaranManualController::class, 'getStudents']);
        Route::get('payment/list', [PembayaranManualController::class, 'getStudentPaymentList']);
        Route::post('payment/add', [PembayaranManualController::class, 'payManually']);

        Route::get('pembayaran-kategori', [PembayaranKategoriController::class, 'index']);
        Route::post('pembayaran-kategori', [PembayaranKategoriController::class, 'store']);
        Route::patch('pembayaran-kategori/{id}', [PembayaranKategoriController::class, 'update']);
        Route::delete('pembayaran-kategori/{id}', [PembayaranKategoriController::class, 'destroy']);
    });

    // Role: BENDAHARA
    Route::middleware('checkrole:Bendahara')->prefix('bendahara')->group(function () {
        Route::post('/siswa', [SiswaController::class, 'index']);

        //Laporan Pembayaran Tahunan
        Route::get('/laporan/pdf-tahunan', function (Request $request) {
            $tgl_awal = $request->query('tgl_awal');
            $tgl_akhir = $request->query('tgl_akhir');

            // Ambil data pembayaran siswa
            if ($tgl_awal && $tgl_akhir) {
                $pembayaranSiswa = Siswa::whereBetween('tanggal_pembayaran', [$tgl_awal, $tgl_akhir])->with('kelas', 'orangtua')->get();
                $fileName = "Pembayaran Siswa {$tgl_awal} - {$tgl_akhir}.pdf";
            } else {
                $pembayaranSiswa = Siswa::with('kelas', 'orangtua')->get();
                $fileName = 'Data Keseluruhan Pembayaran Siswa Tahunan.pdf';
            }

            $data = [
                'pembayaranSiswas' => $pembayaranSiswa,
                'sekolah' => \App\Models\Sekolah::first(),
            ];

            $pdf = Pdf::loadView('print.PrintPdfTahunan', $data);

            return $pdf->stream($fileName);
        })->name('print.PrintPdfTahunan');
    });

    //Laporan Pembayaran SPP
    Route::get('/laporan/pdf-spp', function (Request $request) {
        $tgl_awal = $request->query('tgl_awal');
        $tgl_akhir = $request->query('tgl_akhir');

        if ($tgl_awal && $tgl_akhir) {
            $pembayaranSiswa = Siswa::whereBetween('tanggal_pembayaran', [$tgl_awal, $tgl_akhir])->with('kelas', 'orangtua')->get();
            $fileName = "Pembayaran Siswa {$tgl_awal} - {$tgl_akhir}.pdf";
        } else {
            $pembayaranSiswa = Siswa::with('kelas', 'orangtua')->get();
            $fileName = 'Data Keseluruhan Pembayaran Siswa SPP.pdf';
        }

        $data = [
            'pembayaranSiswas' => $pembayaranSiswa,
            'sekolah' => \App\Models\Sekolah::first(),
        ];

        $pdf = Pdf::loadView('print.PrintPdfSPP', $data);

        return $pdf->stream($fileName);
    })->name('print.PrintPdfSPP');
});

// Role: SISWA
Route::middleware('checkrole:Siswa')->prefix('siswa')->group(function () {
    Route::get('/pembayaran-siswa', [PembayaranSiswaController::class, 'index']);
    Route::patch('/pembayaran-siswa/{id}', [PembayaranSiswaController::class, 'update']);
    Route::get('/riwayat-pembayaran', [PembayaranSiswaController::class, 'riwayatPembayaran']);
    Route::get('/riwayat-tagihan', [PembayaranSiswaController::class, 'riwayatTagihan']);
});

// Role: ORANG TUA
Route::middleware('checkrole:Orang Tua')->prefix('orangtua')->group(function () {
    Route::get('/pembayaran-siswa', [PembayaranSiswaController::class, 'index']);
    Route::post('/pembayaran-siswa/{id}/bayar', [PembayaranSiswaController::class, 'bayar']);
    Route::get('/riwayat-pembayaran', [PembayaranSiswaController::class, 'riwayatPembayaran']);
    Route::get('/riwayat-tagihan', [PembayaranSiswaController::class, 'riwayatTagihan']);
});
