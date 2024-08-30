<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsahaPengajuanRequest;
use App\Models\UsahaPengajuan;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LaundryPengajuanController extends Controller
{
    public function getUsahaPengajuan()
    {
        $validator = Validator::make(request()->all(), [
            'usaha' => ['nullable', 'string', 'min:1'],
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }
        $usaha = Auth::user()->usaha->firstOrFail();
            $startDate = request('tanggal_awal');
            $endDate = request('tanggal_akhir');
            $perPage = request('per_page', 10);
        try{
            $pengajuan = $usaha->pengajuan()
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_pe', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            })->paginate($perPage);

            return response()->json(['data' => $pengajuan], Response::HTTP_OK);
        } catch(Exception $e){
            Log::error('getUsahaPengajuan: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data pengajuan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function PengajuanUsaha(UsahaPengajuanRequest $request, $id)
    {
        try {
            $pengajuan = UsahaPengajuan::findOrFail($id);
            $usaha = $pengajuan->usaha;

            if (in_array($pengajuan->status, ['disetujui', 'ditolak'])) {
                return response()->json([
                    'message' => 'Pengajuan sudah diproses!',
                ], Response::HTTP_UNAUTHORIZED);
            }

            switch ($request->status) {
                case 'disetujui':
                    $pengajuan->update([
                        'status' => 'disetujui',
                        'tanggal_selesai' => now(),
                    ]);
                    return response()->json([
                        'message' => 'Pengajuan telah disetujui.',
                        'data' => $pengajuan,
                    ], Response::HTTP_OK);

                case 'ditolak':
                    if (empty($request->alasan_penolakan)) {
                        return response()->json([
                            'message' => 'Alasan penolakan harus diisi jika status adalah ditolak.',
                        ], Response::HTTP_BAD_REQUEST);
                    }

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
                    ], Response::HTTP_BAD_REQUEST);
            }
        } catch (Exception $e) {
            Log::error('PengajuanUsaha: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat memproses pengajuan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}