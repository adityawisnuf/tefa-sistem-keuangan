<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinPengajuanRequest;
use App\Models\KantinPengajuan;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class KantinPengajuanController extends Controller
{
    public function index()
    {
        $perPage = request()->input('per_page', 10);
        $items = KantinPengajuan::latest()->paginate($perPage);
        return response()->json(['data' => $items], Response::HTTP_OK);
    }
    public function create(KantinPengajuanRequest $request)
    {
        $fields = $request->validated();

        try {
            $kantin = Auth::user()->kantin->first();
            $fields['kantin_id'] = $kantin->id;
            $item = KantinPengajuan::create($fields);
            return response()->json(['data' => $item], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal mengirim pengajuan: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function update(Request $request, KantinPengajuan $pengajuan)
    {
        $request->validate([
            'status' => 'required|in:pending,disetujui,ditolak'
        ]);

        $kantin = $pengajuan->kantin;

        if (in_array($pengajuan->status, ['disetujui', 'ditolak'])) {
            return response()->json([
                'message' => 'Pengajuan sudah diproses!',
            ], Response::HTTP_UNAUTHORIZED);
        }

        switch ($pengajuan->status) {
            case 'pending':

                if ($kantin->saldo >= $pengajuan->jumlah_pengajuan) {
                    $kantin->saldo -= $pengajuan->jumlah_pengajuan;
                    $kantin->save();

                    if ($request->status === 'disetujui') {
                        $pengajuan->update(['status' => 'disetujui']);
                        return response()->json([
                            'message' => 'Pengajuan telah disetujui.',
                            'data' => $pengajuan,
                        ], Response::HTTP_OK);
                    } elseif ($request->status === 'ditolak') {

                        $kantin->saldo += $pengajuan->jumlah_pengajuan;
                        $kantin->save();
                        $pengajuan->update(['status' => 'ditolak']);
                        return response()->json([
                            'message' => 'Pengajuan telah ditolak dan saldo dikembalikan.',
                            'data' => $pengajuan,
                        ], Response::HTTP_OK);
                    }
                } else {
                    return response()->json([
                        'message' => 'Saldo tidak mencukupi untuk pengajuan ini.',
                    ], Response::HTTP_BAD_REQUEST);
                }
                break;

            default:
                return response()->json([
                    'message' => 'Status tidak valid.',
                ], Response::HTTP_UNAUTHORIZED);
        }
    }


}
