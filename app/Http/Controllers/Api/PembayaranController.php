<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\PembayaranSiswa;
use App\Models\Sekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function getYears(Request $request)
    {
        $currentYear = now()->year;
        $years = range($currentYear - 10, $currentYear + 10);

        return response()->json([
            'success' => true,
            'data' => $years,
        ]);
    }

    public function index(Request $request)
    {
        // Validasi parameter input
        $currentYear = now()->year; // Dapatkan tahun saat ini

        $validate = $request->validate([
            'kode_transaksi' => ['string', 'nullable'],
            'nama_siswa' => ['nullable'],
            'kelas' => ['nullable'],
            'jenis_pembayaran' => ['string', 'nullable'],
            'tanggal_pembayaran' => ['date', 'nullable'],
            'bulan_pembayaran' => ['integer', 'nullable', 'between:1,12'],
            'tahun_pembayaran' => ['integer', 'nullable', 'min:' . ($currentYear - 10), 'max:' . ($currentYear + 10)],
            'rekapitulasi' => ['string', 'nullable', 'in:Bulanan,Tahunan,Harian,Semua'],
        ]);

        // Query untuk mengambil data pembayaran siswa
        $query = PembayaranSiswa::with('pembayaran_kategori', 'pembayaran', 'siswa.kelas')->latest();

        // Filter berdasarkan berbagai parameter
        if ($request->filled('kode_transaksi')) {
            $query->where('merchant_order_id', 'like', '%' . $request->kode_transaksi . '%');
        }

        if ($request->filled('nama_siswa')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id', $request->nama_siswa);
            });
        }

        if ($request->filled('kelas')) {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('id', $request->kelas);
            });
        }

        if ($request->filled('jenis_pembayaran')) {
            $query->whereHas('pembayaran.pembayaran_kategori', function ($q) use ($request) {
                $q->where('jenis_pembayaran', $request->jenis_pembayaran);
            });
        }

        // Logika rekapitulasi berdasarkan input pengguna
        if ($request->rekapitulasi !== 'Semua') {
            if ($request->rekapitulasi === 'Harian') {
                $query->whereDate('created_at', $request->filled('tanggal_pembayaran') ? $request->tanggal_pembayaran : now());
            } elseif ($request->rekapitulasi === 'Bulanan') {
                $query->whereMonth('created_at', $request->filled('bulan_pembayaran') ? $request->bulan_pembayaran : now()->month)
                    ->whereYear('created_at', $request->filled('tahun_pembayaran') ? $request->tahun_pembayaran : now()->year);
            } elseif ($request->rekapitulasi === 'Tahunan') {
                $query->whereYear('created_at', $request->filled('tahun_pembayaran') ? $request->tahun_pembayaran : now()->year);
            }
        }

        // Mengambil data pembayaran dengan pagination
        $allPayment = $query->paginate(10);

        // Mengembalikan respon JSON
        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendapatkan data kas',
            'data' => $allPayment,
        ]);
    }


    public function report(Request $request)
    {
        $query = PembayaranSiswa::with('pembayaran_kategori', 'pembayaran', 'siswa.kelas')->latest();

        // Filter berdasarkan berbagai parameter
        if ($request->filled('kode_transaksi') && $request->input('kode_transaksi') !== null && $request->input('kode_transaksi') !== "null") {
            $query->where('merchant_order_id', 'like', '%' . $request->kode_transaksi . '%');
        }

        if ($request->filled('nama_siswa') && $request->input('nama_siswa') !== null && $request->input('nama_siswa') !== "null") {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id', $request->nama_siswa);
            });
        }

        if ($request->filled('kelas') && $request->input('kelas') !== null && $request->input('kelas') !== "null") {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('id', $request->kelas);
            });
        }

        if ($request->filled('jenis_pembayaran') && $request->input('jenis_pembayaran') !== null && $request->input('jenis_pembayaran') !== "null") {
            $query->whereHas('pembayaran.pembayaran_kategori', function ($q) use ($request) {
                $q->where('jenis_pembayaran', $request->jenis_pembayaran);
            });
        }

        // Logika rekapitulasi berdasarkan input pengguna
        if ($request->rekapitulasi !== 'Semua') {
            if ($request->rekapitulasi === 'Harian') {
                $query->whereDate('created_at', $request->filled('tanggal_pembayaran') ? $request->tanggal_pembayaran : now());
            } elseif ($request->rekapitulasi === 'Bulanan') {
                $query->whereMonth('created_at', $request->filled('bulan_pembayaran') ? $request->bulan_pembayaran : now()->month)
                    ->whereYear('created_at', $request->filled('tahun_pembayaran') ? $request->tahun_pembayaran : now()->year);
            } elseif ($request->rekapitulasi === 'Tahunan') {
                $query->whereYear('created_at', $request->filled('tahun_pembayaran') ? $request->tahun_pembayaran : now()->year);
            }
        }

        $allPayment = $query->get();
        $fileName = "Rekap_Pembayaran.pdf";

        $data = ['pembayarans' => $allPayment, 'sekolah' => Sekolah::first()];
        $pdf = Pdf::loadView('print.pembayaran', $data);

        return $pdf->stream($fileName);
    }
    public function getPembayaran(Request $request)
    {
        $query = PembayaranSiswa::query();

        // Filter berdasarkan nama siswa (ID siswa)
        if ($request->filled('nama_siswa')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id', $request->nama_siswa);
            });
        }
        // Debugging
        dd($query->toSql(), $query->getBindings()); // Ini akan menghentikan eksekusi dan menampilkan query dan binding-nya

        // Jika Anda melanjutkan dari sini, itu berarti Anda telah memeriksa query
        $data = $query->paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendapatkan data kas',
            'data' => $data,
        ]);
    }
}
