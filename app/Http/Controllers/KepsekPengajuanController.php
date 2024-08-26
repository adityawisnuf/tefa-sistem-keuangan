<?php

namespace App\Http\Controllers;

use App\Models\UsahaPengajuan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class KepsekPengajuanController extends Controller
{
    public function getUsahaPengajuan()
    {
        $validator = Validator::make(request()->all(), [
            'tahun' => ['nullable', 'integer', 'min:1900', 'max:' . Carbon::now()->year],
            'bulan' => ['nullable', 'integer', 'min:1', 'max:12'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'role' => ['nullable', 'in:Kantin,Laundry'],
            'nama_usaha' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $year = request('tahun', Carbon::now()->year);
        $month = request('bulan', Carbon::now()->month);
        $nama_usaha = request('nama_usaha');
        $role = request('role', 'Kantin');
        $perPage = request('per_page', 10);

        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $pengajuan = UsahaPengajuan::with(['usaha.user'])
            ->whereBetween('tanggal_selesai', [$startDate, $endDate])
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->whereRelation('usaha.user', 'role', 'like', "%$role%")
            ->when($nama_usaha, function ($query) use ($nama_usaha) {
                $query->whereRelation('usaha', 'nama_usaha','like', "%$nama_usaha%")
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
    }
}
