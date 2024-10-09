<?php

namespace App\Http\Controllers;

use App\Http\Services\SocketIOService;
use App\Models\UsahaPengajuan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class BendaharaPengajuanController extends Controller
{
    public function getUsahaPengajuan(Request $request)
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'role' => ['nullable', 'in:Kantin,Laundry'],
            'status' => ['nullable', 'in:aktif,selesai'],
            'nama_usaha' => ['nullable', 'string'],
        ]);

        $startDate = $validated['tanggal_awal'] ?? null;
        $endDate = $validated['tanggal_akhir'] ?? null;
        $nama_usaha = $validated['nama_usaha'] ?? null;
        $role = $validated['role'] ?? 'Kantin';
        $status = $validated['status'] ?? 'aktif';
        $perPage = $validated['per_page'] ?? 10;

        $pengajuan = UsahaPengajuan
            ::select('id', 'usaha_id', 'jumlah_pengajuan', 'status', 'alasan_penolakan', 'tanggal_pengajuan', 'tanggal_selesai')
            ->with(
                'usaha:id,user_id,nama_usaha',
                'usaha.user:id,role'
            )
            ->whereIn('status', ['pending'])
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_pengajuan', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            })
            ->when($status === 'aktif', function ($query) {
                $query->whereIn('status', ['pending']);
            })
            ->when($status === 'selesai', function ($query) {
                $query->whereIn('status', ['disetujui', 'ditolak']);
            })
            ->whereRelation('usaha.user', 'role', 'like', "%$role%")
            ->when($nama_usaha, function ($query) use ($nama_usaha) {
                $query->whereRelation('usaha', 'nama_usaha', 'like', "%$nama_usaha%");
            })
            ->paginate($perPage);

        $pengajuan->getCollection()->transform(function ($pengajuan) {
            return array_merge(
                collect($pengajuan)->forget('usaha')->toArray(),
                collect($pengajuan->usaha)->forget(['id', 'user'])->toArray(),
            );
        });

        return response()->json(['data' => $pengajuan], Response::HTTP_OK);
    }

    public function confirmUsahaPengajuan(Request $request, $id, SocketIOService $socketIOService)
    {
        $validated = $request->validate([
            'alasan_penolakan' => [Rule::requiredIf($request->input('status') == 'ditolak')],
            'status' => ['required', Rule::in('disetujui', 'ditolak')],
        ]);

        $pengajuan = UsahaPengajuan::findOrFail($id);
        $usaha = $pengajuan->usaha;

        if ($pengajuan->status == 'disetujui' || $pengajuan->status == 'ditolak') {
            return response()->json(['message' => 'Pengajuan sudah diproses!'], Response::HTTP_BAD_REQUEST);
        }

        switch ($validated['status']) {
            case 'disetujui':
                $pengajuan->update([
                    'status' => 'disetujui',
                    'tanggal_selesai' => now(),
                ]);

                return response()->json(['message' => 'Pengajuan telah disetujui.'], Response::HTTP_OK);

            case 'ditolak':
                if ($validated['alasan_penolakan'] == '') {
                    return response()->json(['message' => 'Alasan penolakan harus diisi jika status adalah ditolak.'], Response::HTTP_BAD_REQUEST);
                }

                DB::beginTransaction();
                $usaha->update([
                    'saldo' => $usaha->saldo + $pengajuan->jumlah_pengajuan
                ]);

                $pengajuan->update([
                    'status' => 'ditolak',
                    'alasan_penolakan' => $validated['alasan_penolakan'],
                    'tanggal_selesai' => now(),
                ]);
                DB::commit();
                break;

            default:
                return response()->json(['message' => 'Status tidak valid.'], Response::HTTP_BAD_REQUEST);
        }

        $socketIOService->remindFetch($usaha->user->id);

        return response()->json(['message' => 'Pengajuan telah ditolak dan saldo dikembalikan.'], Response::HTTP_OK);
    }
}