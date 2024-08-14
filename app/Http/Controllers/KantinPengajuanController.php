<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinPengajuanRequest;
use App\Models\KantinPengajuan;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KantinPengajuanController extends Controller
{
    public function index()
    {
        $perPage = request()->input('per_page', 10);
        $items = KantinPengajuan::latest()->paginate($perPage);
        return response()->json(['data' => $items], Response::HTTP_OK);
    }

    public function create(KantinPengajuanRequest $request)
    {
        $fields = $request->validated();

        try {
            $kantin = Auth::user()->kantin->first();

            if ($kantin->saldo < $fields['jumlah_pengajuan']) {
                return response()->json([
                    'message' => 'Saldo tidak mencukupi untuk pengajuan ini.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $fields['kantin_id'] = $kantin->id;

            // Buat pengajuan
            DB::beginTransaction();
            $item = KantinPengajuan::create($fields);

            // Kurangi saldo jika pengajuan berhasil dibuat
            $kantin->saldo -= $fields['jumlah_pengajuan'];
            $kantin->save();
            DB::commit();

            return response()->json(['data' => $item], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengirim pengajuan: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, KantinPengajuan $pengajuan)
    {
        // Validasi input
        $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'alasan_penolakan' => 'nullable|string'
        ]);

        // Ambil data kantin
        $kantin = $pengajuan->kantin;

        if (!$kantin) {
            return response()->json([
                'message' => 'Kantin tidak ditemukan.',
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

}
