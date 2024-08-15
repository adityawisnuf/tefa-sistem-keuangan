<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinPengajuanRequest;
use App\Models\KantinPengajuan;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $kantin = Auth::user()->kantin->first();
        $fields = $request->validated();

        try {
            if ($kantin->saldo < $fields['jumlah_pengajuan']) {
                return response()->json([
                    'message' => 'Saldo tidak mencukupi untuk pengajuan ini.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $fields['kantin_id'] = $kantin->id;

            // Buat pengajuan
            DB::beginTransaction();
            $pengajuan = KantinPengajuan::create($fields);
            $kantin->saldo -= $fields['jumlah_pengajuan'];
            $kantin->save();
            DB::commit();

            return response()->json(['data' => $pengajuan], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengirim pengajuan: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    

}
