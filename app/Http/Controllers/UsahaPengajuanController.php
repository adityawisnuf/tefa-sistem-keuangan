<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsahaPengajuanRequest;
use App\Models\UsahaPengajuan;
use Carbon\Carbon;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsahaPengajuanController extends Controller
{
    public function index()
    {
        $validator = Validator::make(request()->all(), [
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:aktif,selesai'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }


        $usaha = Auth::user()->usaha->firstOrFail();

        $startDate = request('tanggal_awal');
        $endDate = request('tanggal_akhir');
        $perPage = request()->input('per_page', 10);
        $status = request('status', 'aktif');

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
            Log::error('index: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data pengajuan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }




    public function create(UsahaPengajuanRequest $request)
    {
        $usaha = Auth::user()->usaha->firstOrFail();
        $fields = $request->validated();

        try {
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
        } catch (Exception $e) {
            Log::error('create: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat membuat pengajuan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}