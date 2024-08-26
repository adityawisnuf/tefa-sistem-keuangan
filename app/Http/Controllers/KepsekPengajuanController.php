<?php

namespace App\Http\Controllers;

use App\Models\UsahaPengajuan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class KepsekPengajuanController extends Controller
{
    public function getUsahaPengajuan()
    {
        $validator = Validator::make(request()->all(), [
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'role' => ['nullable', 'in:Kantin,Laundry'],
            'nama_usaha' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $startDate = request('tanggal_awal');
        $endDate = request('tanggal_akhir');
        $nama_usaha = request('nama_usaha');
        $role = request('role', 'Kantin');
        $perPage = request('per_page', 10);

        try {
            $pengajuan = UsahaPengajuan::with(['usaha.user'])
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('tanggal_selesai', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                }, function ($query) {
                    $query->whereBetween('tanggal_selesai', [
                        Carbon::now()->startOfMonth(),
                        Carbon::now()->endOfMonth()
                    ]);
                })
                ->whereIn('status', ['disetujui', 'ditolak'])
                ->whereRelation('usaha.user', 'role', 'like', "%$role%")
                ->when($nama_usaha, function ($query) use ($nama_usaha) {
                    $query->whereRelation('usaha', 'nama_usaha', 'like', "%$nama_usaha%");
                })
                ->paginate($perPage);

            $pengajuan->getCollection()->transform(function ($pengajuan) {
                return [
                    'id' => $pengajuan->id,
                    'nama_usaha' => $pengajuan->usaha->nama_usaha,
                    'jumlah_pengajuan' => $pengajuan->jumlah_pengajuan,
                    'status' => $pengajuan->status,
                    'alasan_penolakan' => $pengajuan->alasan_penolakan,
                    'tanggal_pengajuan' => $pengajuan->tanggal_pengajuan,
                    'tanggal_selesai' => $pengajuan->tanggal_selesai,
                ];
            });

            return response()->json(['data' => $pengajuan], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data pengajuan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
