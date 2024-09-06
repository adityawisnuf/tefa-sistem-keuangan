<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsahaPengajuanRequest;
use App\Http\Services\SocketIOService;
use App\Models\UsahaPengajuan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Process;
use Symfony\Component\HttpFoundation\Response;

class BendaharaPengajuanController extends Controller
{
    public function getUsahaPengajuan()
    {
        $validator = Validator::make(request()->all(), [
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'role' => ['nullable', 'in:Kantin,Laundry'],
            'status' => ['nullable', 'in:active,completed'],
            'nama_usaha' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $startDate = request('tanggal_awal');
        $endDate = request('tanggal_akhir');
        $nama_usaha = request('nama_usaha');
        $role = request('role', 'Kantin');
        $status = request('status', 'active');
        $perPage = request('per_page', 10);
        
        try {

            $pengajuan = UsahaPengajuan::with(['usaha.user'])
                // ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                //     $query->whereBetween('tanggal_pengajuan', [
                //         Carbon::parse($startDate)->startOfDay(),
                //         Carbon::parse($endDate)->endOfDay()
                //     ]);
                // }, function ($query) {
                //     $query->whereBetween('tanggal_pengajuan', [
                //         Carbon::now()->startOfMonth(),
                //         Carbon::now()->endOfMonth()
                //     ]);
                // })
                ->when($status === 'active', function ($query) {
                    $query->whereIn('status', ['pending']);
                })
                ->when('status' === 'completed', function ($query) {
                    $query->whereIn('status', ['disetujui', 'ditolak']);
                })
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
            Log::error('getUsahaPengajuan: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data pengajuan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function confirmUsahaPengajuan(UsahaPengajuanRequest $request, $id, SocketIOService $socketIOService)
    {

        $pengajuan = UsahaPengajuan::findOrFail($id);
        $usaha = $pengajuan->usaha;

        if (in_array($pengajuan->status, ['disetujui', 'ditolak'])) {
            return response()->json([
                'message' => 'Pengajuan sudah diproses!',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
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

                    DB::beginTransaction();

                    $usaha->saldo += $pengajuan->jumlah_pengajuan;
                    $usaha->save();

                    $pengajuan->update([
                        'status' => 'ditolak',
                        'alasan_penolakan' => $request->alasan_penolakan,
                        'tanggal_selesai' => now(),
                    ]);

                    DB::commit();
                    break;

                default:
                    return response()->json([
                        'message' => 'Status tidak valid.',
                    ], Response::HTTP_BAD_REQUEST);
            }

            $socketIOService->remindFetch($usaha->user->id);

            return response()->json([
                'message' => 'Pengajuan telah ditolak dan saldo dikembalikan.',
                'data' => $pengajuan,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('confirmUsahaPengajuan' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat memproses data pengajuan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}