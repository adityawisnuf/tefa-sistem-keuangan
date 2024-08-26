<?php

namespace App\Http\Controllers;

use App\Models\UsahaPengajuan;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KepsekPengajuanController extends Controller
{
    public function getUsahaPengajuan()
    {
        $role = request('role', 'Kantin');
        $perPage = request()->input('per_page', 10);
        $status = request('status', 'all');

        $pengajuan = UsahaPengajuan::with(['usaha.user'])
            ->whereHas('usaha.user', function ($query) use ($role) {
                $query->where('role', 'like', '%' . $role . '%');
            })
            ->when($status === 'aktif', function ($query) {
                return $query->where('usaha_pengajuan.status', 'pending');
            })
            ->when($status === 'selesai', function ($query) {
                return $query->whereIn('usaha_pengajuan.status', ['ditolak', 'disetujui']);
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
