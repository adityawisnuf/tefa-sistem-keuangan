<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryPengajuanRequest;
use App\Models\LaundryPengajuan;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
class LaundryPengajuanController extends Controller
{
    public function index()
    {
        $perPage = request()->input('per_page', 10);
        $items = LaundryPengajuan::latest()->paginate($perPage);
        return response()->json(['data' => $items], Response::HTTP_OK);
    }

    public function create(LaundryPengajuanRequest $request)
    {
        $fields = $request->validated();

        try {
            $laundry = Auth::user()->laundry->first();

            if ($laundry->saldo < $fields['jumlah_pengajuan']) {
                return response()->json([
                    'message' => 'Saldo tidak mencukupi untuk pengajuan ini.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $fields['laundry_id'] = $laundry->id;

            // Buat pengajuan
            DB::beginTransaction();

            $item = LaundryPengajuan::create($fields);

            // Kurangi saldo jika pengajuan berhasil dibuat
            $laundry->saldo -= $fields['jumlah_pengajuan'];
            $laundry->save();
            DB::commit();

            return response()->json(['data' => $item], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengirim pengajuan: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, LaundryPengajuan $pengajuan)
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
