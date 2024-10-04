<?php

namespace App\Http\Controllers;

use App\Http\Services\SocketIOService;
use App\Models\UsahaPengajuan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsahaPengajuanController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:aktif,selesai'],
        ]);

        $usaha = Auth::user()->usaha->firstOrFail();

        $startDate = $validated['tanggal_awal'] ?? null;
        $endDate = $validated['tanggal_akhir'] ?? null;
        $perPage = $validated['per_page'] ?? 10;
        $status = $validated['status'] ?? 'aktif';

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
    }

    public function create(Request $request, SocketIOService $socketIOService)
    {
        $validated = $request->validate([
            'jumlah_pengajuan' => ['required', 'integer', 'min:1'],
        ]);

        $usaha = Auth::user()->usaha;

        if ($usaha->saldo < $validated['jumlah_pengajuan'])
            return response()->json(['message' => 'Saldo tidak mencukupi untuk pengajuan ini.'], Response::HTTP_BAD_REQUEST);

        DB::beginTransaction();

        $validated['usaha_id'] = $usaha->id;
        $pengajuan = UsahaPengajuan::create($validated);
        $usaha->update([
            'saldo' => $usaha->saldo - $validated['jumlah_pengajuan']
        ]);

        DB::commit();

        $bendahara = User::where('role', 'Bendahara')->first();
        $socketIOService->remindFetch($bendahara->id);

        return response()->json(['data' => $pengajuan], Response::HTTP_CREATED);
    }
}
