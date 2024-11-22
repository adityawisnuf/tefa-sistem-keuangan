<?php

namespace App\Http\Controllers;

use App\Exports\PembayaranSiswaTahunanExport;
use App\Exports\TahunanExcelExport;
use App\Models\Pembayaran;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class PrintExcelTahunanController extends Controller
{
    public function exportExce(Request $request)
    {
        Log::info("File Excel Tahunan diakses oleh pengguna dengan IP: " . $request->ip());

        // Mengambil data siswa dengan filter sesuai permintaan
        $siswas = Siswa::with(['kelas', 'orangtua'])
            ->when($request->filled('nama_siswa') && $request->nama_siswa != "null", function ($query) use ($request) {
                $query->where('id', $request->nama_siswa);
            })
            ->when($request->filled('kelas') && $request->kelas != "null", function ($query) use ($request) {
                $query->whereHas('kelas', function ($q) use ($request) {
                    $q->where('id', $request->kelas);
                });
            })
            ->when($request->filled('jurusan') && $request->jurusan != "null", function ($query) use ($request) {
                $query->whereHas('kelas', function ($q) use ($request) {
                    $q->where('jurusan', $request->jurusan);
                });
            })
            ->get();

        $result = [];

        foreach ($siswas as $siswa) {
            // Ambil data pembayaran tahunan untuk setiap siswa
            $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
                $query->where('jenis_pembayaran', 2) // Jenis tahunan
                      ->where('status', 1); // Status aktif
            })
            ->where('siswa_id', $siswa->id)
            ->with(['pembayaran_siswa', 'pembayaran_kategori'])
            ->get();

            $payments = [];
            $totalTagihan = 0;

            foreach ($pembayaranList as $pembayaran) {
                $status = 'Belum Lunas';
                if ($pembayaran->pembayaran_siswa->isNotEmpty()) {
                    $status = $pembayaran->pembayaran_siswa->first()->status == 1 ? 'Lunas' : 'Belum Lunas';
                }

                $nominal = $pembayaran->nominal;
                if ($status === 'Belum Lunas') {
                    $totalTagihan += $nominal;
                }

                $payments[] = [
                    'pembayaran_ke' => $pembayaran->pembayaran_kategori->nama ?? 'Nama Tidak Tersedia',
                    'nominal' => $nominal,
                    'status' => $status,
                ];
            }

            $result[] = [
                'nama_siswa' => $siswa->nama_depan . ' ' . ($siswa->nama_belakang ?? ''),
                'kelas' => $siswa->kelas->kelas ?? '',
                'jurusan' => $siswa->kelas->jurusan ?? '',
                'telepon' => $siswa->telepon,
                'orangtua' => $siswa->orangtua->nama ?? '',
                'sisa_tagihan' => $totalTagihan,
                'payments' => $payments,
            ];
        }

        // Unduh data dalam bentuk Excel menggunakan Export class
        return Excel::download(new TahunanExcelExport($result), 'Pembayaran-Tahunan-Siswa.xlsx');
    }
}
