<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinPengajuanRequest;
use App\Models\KantinPengajuan;
use App\Models\KantinTransaksi;
use App\Models\LaundryPengajuan;
use App\Models\LaundryTransaksiSatuan;
use App\Models\LaundryTransaksiKiloan;
use DateTime;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BendaharaController extends Controller
{
    private $startOfWeek;
    private $endOfWeek;

    public function __construct()
    {
        $this->startOfWeek = now()->startOfWeek();
        $this->endOfWeek = now()->endOfWeek();
    }

    public function index()
    {
        return response()->json([
            'data' => [
                'kantin_transaksi' => $this->getKantinTransaksi(),
                'laundry_transaksi_satuan' => $this->getLaundryTransaksiSatuan(),
                'laundry_transaksi_kiloan' => $this->getLaundryTransaksiKiloan(),
            ]
        ], Response::HTTP_OK);
    }

    public function getKantinTransaksi()
{
    $perPage = request()->input('per_page', 10);
    return KantinTransaksi::whereIn('status', ['dibatalkan', 'selesai'])
        ->whereBetween('tanggal_selesai', [$this->startOfWeek, $this->endOfWeek])
        ->paginate($perPage);
}

public function getLaundryTransaksiSatuan()
{
    $perPage = request()->input('per_page', 10);
    return LaundryTransaksiSatuan::whereIn('status', ['dibatalkan', 'selesai'])
        ->whereBetween('tanggal_selesai', [$this->startOfWeek, $this->endOfWeek])
        ->paginate($perPage);
}

public function getLaundryTransaksiKiloan()
{
    $perPage = request()->input('per_page', 10);
    return LaundryTransaksiKiloan::whereIn('status', ['dibatalkan', 'selesai'])
        ->whereBetween('tanggal_selesai', [$this->startOfWeek, $this->endOfWeek])
        ->paginate($perPage);
}


    public function getKantinPengajuan() {
        $perPage = request()->input('per_page', 10);
        return KantinPengajuan::paginate($perPage);
    }
    public function getLaundryPengajuan() {
        $perPage = request()->input('per_page', 10);
        return LaundryPengajuan::paginate($perPage);
    }

    public function PengajuanKantin(KantinPengajuanRequest $request, KantinPengajuan $pengajuan)
    {
        // Ambil data kantin
        $kantin = $pengajuan->kantin;

        // Periksa apakah pengajuan sudah diproses
        if (in_array($pengajuan->status, ['disetujui', 'ditolak'])) {
            return response()->json([
                'message' => 'Pengajuan sudah diproses!',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Logika untuk mengupdate status
        switch ($request->status) {
            case 'disetujui':
                // Tidak perlu mengurangi saldo lagi, karena sudah dikurangi saat status 'pending'
                $pengajuan->update([
                    'status' => 'disetujui',
                    'tanggal_selesai' => now(),
                ]);
                return response()->json([
                    'message' => 'Pengajuan telah disetujui.',
                    'data' => $pengajuan,
                ], Response::HTTP_OK);

            case 'ditolak':
                // Validasi alasan penolakan
                if (empty($request->alasan_penolakan)) {
                    return response()->json([
                        'message' => 'Alasan penolakan harus diisi jika status adalah ditolak.',
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Kembalikan saldo
                $kantin->saldo += $pengajuan->jumlah_pengajuan;
                $kantin->save();

                $pengajuan->update([
                    'status' => 'ditolak',
                    'alasan_penolakan' => $request->alasan_penolakan,
                    'tanggal_selesai' => now(),
                ]);
                return response()->json([
                    'message' => 'Pengajuan telah ditolak dan saldo dikembalikan.',
                    'data' => $pengajuan,
                ], Response::HTTP_OK);

            default:
                return response()->json([
                    'message' => 'Status tidak valid.',
                ], Response::HTTP_UNAUTHORIZED);
        }
    }

    public function PengajuanLaundry(Request $request, LaundryPengajuan $pengajuan)
    {
        // Validasi input
        $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'alasan_penolakan' => 'nullable|string'
        ]);

        // Ambil data laundry
        $laundry = $pengajuan->laundry;

        if (!$laundry) {
            return response()->json([
                'message' => 'laundry tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Periksa apakah pengajuan sudah diproses
        if (in_array($pengajuan->status, ['disetujui', 'ditolak'])) {
            return response()->json([
                'message' => 'Pengajuan sudah diproses!',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Logika untuk mengupdate status
        switch ($request->status) {

            case 'disetujui':
                // Tidak perlu mengurangi saldo lagi, karena sudah dikurangi saat status 'pending'
                $pengajuan->update([
                    'status' => 'disetujui',
                    'tanggal_selesai' => now(),
                ]);
                return response()->json([
                    'message' => 'Pengajuan telah disetujui.',
                    'data' => $pengajuan,
                ], Response::HTTP_OK);

            case 'ditolak':
                // Validasi alasan penolakan
                if (empty($request->alasan_penolakan)) {
                    return response()->json([
                        'message' => 'Alasan penolakan harus diisi jika status adalah ditolak.',
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Kembalikan saldo
                $laundry->saldo += $pengajuan->jumlah_pengajuan;
                $laundry->save();

                $pengajuan->update([
                    'status' => 'ditolak',
                    'alasan_penolakan' => $request->alasan_penolakan,
                    'tanggal_selesai' => now(),
                ]);
                return response()->json([
                    'message' => 'Pengajuan telah ditolak dan saldo dikembalikan.',
                    'data' => $pengajuan,
                ], Response::HTTP_OK);

            default:
                return response()->json([
                    'message' => 'Status tidak valid.',
                ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
