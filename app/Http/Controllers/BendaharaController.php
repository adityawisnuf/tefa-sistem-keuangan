<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinPengajuanRequest;
use App\Http\Requests\UsahaPengajuanRequest;
use App\Models\KantinPengajuan;
use App\Models\KantinTransaksi;
use App\Models\LaundryPengajuan;
use App\Models\LaundryTransaksi;
use App\Models\UsahaPengajuan;
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

    public function getKantinTransaksi()
    {
        $perPage = request()->input('per_page', 10);
    
        $transaksi = KantinTransaksi::with('kantin_transaksi_detail')
            ->whereIn('status', ['dibatalkan', 'selesai'])
            ->whereBetween('tanggal_selesai', [$this->startOfWeek, $this->endOfWeek])
            ->paginate($perPage);
        
        // Transform the data to the desired format
        $formattedData = $transaksi->flatMap(function ($item) {
            return $item->kantin_transaksi_detail->map(function ($detail) use ($item) {
                return [
                    'id' => $item->id, // ID transaksi kantin
                    'siswa_id' => $item->siswa_id, 
                    'usaha_id' => $item->usaha_id,
                    'jumlah' => $detail->jumlah,
                    'harga' => $detail->harga,
                    'harga_total' => $detail->harga * $detail->jumlah,

                    'status' => $item->status,
                    'tanggal_pemesanan' => $item->tanggal_pemesanan,
                    'tanggal_selesai' => $item->tanggal_selesai,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });
        });
    
        return response()->json(['data' => $formattedData], Response::HTTP_OK);
    }

    public function getLaundryTransaksi()
    {
        $perPage = request()->input('per_page', 10);
        return LaundryTransaksi::whereIn('status', ['dibatalkan', 'selesai'])
            ->whereBetween('tanggal_selesai', [$this->startOfWeek, $this->endOfWeek])
            ->paginate($perPage);
    }

    public function getUsahaPengajuan()
    {
        $perPage = request()->input('per_page', 10);
        return UsahaPengajuan::paginate($perPage);
    }

    public function PengajuanUsaha(UsahaPengajuanRequest $request, UsahaPengajuan $pengajuan)
    {
        // Ambil data usaha
        $usaha = $pengajuan->usaha;

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
                $usaha->saldo += $pengajuan->jumlah_pengajuan;
                $usaha->save();

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