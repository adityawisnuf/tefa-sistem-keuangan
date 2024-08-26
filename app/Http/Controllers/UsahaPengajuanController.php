<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsahaPengajuanRequest;
use App\Models\UsahaPengajuan;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsahaPengajuanController extends Controller
{
    public function index()
    {
        $usaha = Auth::user()->usaha->firstOrFail();

        $perPage = request()->input('per_page', 10);
        $status = request('status', 'all');

        $pengajuan = UsahaPengajuan::where('usaha_id', $usaha->id)
            ->when($status === 'aktif', function ($query) {
                return $query->where('status', 'pending');
            })
            ->when($status === 'selesai', function ($query) {
                return $query->whereIn('status', ['ditolak', 'disetujui']);
            })
            ->with('usaha:id,nama_usaha') // Mengambil nama usaha langsung
            ->paginate($perPage, ['id', 'usaha_id', 'jumlah_pengajuan', 'status', 'alasan_penolakan', 'tanggal_pengajuan', 'tanggal_selesai']);

        $pengajuan->getCollection()->transform(function($pengajuan) {
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




    public function create(UsahaPengajuanRequest $request)
    {
        $usaha = Auth::user()->usaha->firstOrFail();
        $fields = $request->validated();

        if ($usaha->saldo < $fields['jumlah_pengajuan']) {
            return response()->json([
                'message' => 'Saldo tidak mencukupi untuk pengajuan ini.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $fields['usaha_id'] = $usaha->id;

        DB::beginTransaction();
        $pengajuan = UsahaPengajuan::create($fields);
        $usaha->update([
            'saldo' => $usaha->saldo - $fields['jumlah_pengajuan']
        ]);
        DB::commit();

        return response()->json(['data' => [array_merge(['nama_usaha' => $usaha->nama_usaha], $pengajuan->toArray())]], Response::HTTP_CREATED);
    }
}
