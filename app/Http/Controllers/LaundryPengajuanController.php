<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsahaPengajuanRequest;
use App\Models\UsahaPengajuan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LaundryPengajuanController extends Controller
{
    public function getUsahaPengajuan()
    {
        $usaha = Auth::user()->usaha->firstOrFail();
        $startDate = request('tanggal_awal');
        $endDate = request('tanggal_akhir');
        $perPage = request('per_page', 10);

        $pengajuan = $usaha->pengajuan()
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_pe', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            })->paginate($perPage);

        return response()->json(['data' => $pengajuan], Response::HTTP_OK);
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
