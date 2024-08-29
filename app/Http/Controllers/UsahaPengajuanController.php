<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsahaPengajuanRequest;
use App\Models\UsahaPengajuan;
use Carbon\Carbon;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsahaPengajuanController extends Controller
{
    public function index()
    {
        $usaha = Auth::user()->usaha->firstOrFail();

        $startDate = request('tanggal_awal');
        $endDate = request('tanggal_akhir');
        $perPage = request()->input('per_page', 10);
        $status = request('status', 'all');

        try {
            $pengajuan = $usaha->usaha_pengajuan()
                ->select(['jumlah_pengajuan', 'status', 'alasan_penolakan', 'tanggal_pengajuan', 'tanggal_selesai'])
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('tanggal_pengajuan', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                })
                ->when($status === 'aktif', function ($query) {
                    return $query->where('status', 'pending');
                })
                ->when($status === 'selesai', function ($query) {
                    return $query->whereIn('status', ['ditolak', 'disetujui']);
                })
                ->paginate($perPage);
    
            return response()->json(['data' => $pengajuan], Response::HTTP_OK);
        } catch (Exception $e) {
            
        }
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
