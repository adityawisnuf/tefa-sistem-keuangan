<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Sekolah;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PrintPdfSPPController extends Controller
{
    public function cetakSiswaPembayaran(Request $request)
    {
        Log::info("File PDF diakses oleh pengguna dengan IP: " . $request->ip());
        // Memulai query untuk mengambil data siswa dengan filter yang diminta
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
            $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
                $query->where('jenis_pembayaran', 1) // Jenis SPP
                    ->where('status', 1); // Status aktif
            })
                ->where('siswa_id', $siswa->id)
                ->with(['pembayaran_siswa' => function ($query) use ($siswa) {
                    $query->where('siswa_id', $siswa->id)
                        ->with('pembayaran_siswa_cicilan');
                }, 'pembayaran_kategori'])
                ->get();

            $payments = [];
            $totalTagihan = 0;

            foreach ($pembayaranList as $pembayaran) {
                $pembayaran_siswa = $pembayaran->pembayaran_siswa->first();
                $nominal = $pembayaran->nominal;
                $status = 'Belum Lunas';

                if ($pembayaran_siswa && $pembayaran_siswa->status == 1) {
                    $status = 'Lunas';
                } else {
                    $totalTagihan += $nominal;
                }

                $payments[] = [
                    'pembayaran_ke' => $pembayaran->pembayaran_ke,
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

        // Mengambil data sekolah
        $sekolah = Sekolah::first();
        // Generate PDF dan kirim data ke view
        $pdf = Pdf::loadView('print.PrintPdfSPP', compact('result', 'sekolah'));
        return $pdf->stream('Pembayaran-Siswa-SPP.pdf');
    }
}

