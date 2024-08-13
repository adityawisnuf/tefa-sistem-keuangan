<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryPengajuanRequest;
use App\Models\LaundryPengajuan;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
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

            // Validasi apakah saldo mencukupi
            if ($laundry->saldo < $fields['jumlah_pengajuan']) {
                return response()->json([
                    'message' => 'Saldo tidak mencukupi untuk pengajuan ini.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $fields['laundry_id'] = $laundry->id;
            $item = LaundryPengajuan::create($fields);

            return response()->json(['data' => $item], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal mengirim pengajuan: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, LaundryPengajuan $pengajuan)
    {
        // Validasi input
        $request->validate([
            'status' => 'required|in:pending,disetujui,ditolak',
            'alasan_penolakan' => 'nullable|string'
        ]);

        // Ambil data laundry
        $laundry = $pengajuan->laundry;

        // Periksa apakah pengajuan sudah diproses
        if (in_array($pengajuan->status, ['disetujui', 'ditolak'])) {
            return response()->json([
                'message' => 'Pengajuan sudah diproses!',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Logika untuk mengupdate status
        switch ($request->status) {
            case 'pending':
                // Tidak perlu penanganan khusus jika statusnya 'pending'
                break;

            case 'disetujui':
                if ($laundry->saldo >= $pengajuan->jumlah_pengajuan) {
                    $laundry->saldo -= $pengajuan->jumlah_pengajuan;
                    $laundry->save();

                    $pengajuan->update([
                        'status' => 'disetujui',
                        'tanggal_selesai' => now(),
                    ]);
                    return response()->json([
                        'message' => 'Pengajuan telah disetujui.',
                        'data' => $pengajuan,
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => 'Saldo tidak mencukupi untuk pengajuan ini.',
                    ], Response::HTTP_BAD_REQUEST);
                }
                break;

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
                break;

            default:
                return response()->json([
                    'message' => 'Status tidak valid.',
                ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
