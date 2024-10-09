<?php

namespace App\Http\Controllers;

use App\Models\UsahaPengajuan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KepsekPengajuanController extends Controller
{
    public function getUsahaPengajuan(Request $request)
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'role' => ['nullable', 'in:Kantin,Laundry'],
            'nama_usaha' => ['nullable', 'string'],
        ]);

        $startDate = $validated['tanggal_awal'] ?? null;
        $endDate = $validated['tanggal_akhir'] ?? null;
        $nama_usaha = $validated['nama_usaha'] ?? null;
        $role = $validated['role'] ?? 'Kantin';
        $perPage = $validated['per_page'] ?? 10;

        $pengajuan = UsahaPengajuan
            ::select('id', 'usaha_id', 'jumlah_pengajuan', 'status', 'alasan_penolakan', 'tanggal_pengajuan', 'tanggal_selesai')
            ->with(
                'usaha:id,user_id,nama_usaha',
                'usaha.user:id,role'
            )
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_selesai', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            })
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->whereRelation('usaha.user', 'role', 'like', "%$role%")
            ->when($nama_usaha, function ($query) use ($nama_usaha) {
                $query->whereRelation('usaha', 'nama_usaha', 'like', "%$nama_usaha%");
            })
            ->paginate($perPage);

        $pengajuan->getCollection()->transform(function ($pengajuan) {
            return array_merge(
                collect($pengajuan)->forget('usaha')->toArray(),
                ['nama_usaha' => $pengajuan->usaha->nama_usaha]
            );
        });

        return response()->json(['data' => $pengajuan], Response::HTTP_OK);
    }
}