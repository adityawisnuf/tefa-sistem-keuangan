<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsahaPengajuanRequest;
use App\Models\UsahaPengajuan;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BendaharaPengajuanController extends Controller
{
    public function getUsahaPengajuan()
    {
        $role = request('role', '');
        $perPage = request()->input('per_page', 10);

        $pengajuan = UsahaPengajuan::join('usaha', 'usaha.id', '=', 'usaha_pengajuan.usaha_id')
        ->join('users', 'users.id', '=', 'usaha.user_id')
        ->select('usaha_pengajuan.id',
            'usaha.nama_usaha',
            'usaha_pengajuan.jumlah_pengajuan',
            'usaha_pengajuan.status',
            'usaha_pengajuan.alasan_penolakan',
            'usaha_pengajuan.tanggal_pengajuan')
        ->where('users.role', 'like', '%' . $role . '%')
        ->paginate($perPage);

        return response()->json(['data' => $pengajuan], Response::HTTP_OK);
    }

    public function confirmUsahaPengajuan(UsahaPengajuanRequest $request, UsahaPengajuan $pengajuan)
    {
        $usaha = $pengajuan->usaha;

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