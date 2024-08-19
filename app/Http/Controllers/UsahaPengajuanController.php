<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinPengajuanRequest;
use App\Http\Requests\UsahaPengajuanRequest;
use App\Models\KantinPengajuan;
use App\Models\UsahaPengajuan;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsahaPengajuanController extends Controller
{
    public function index()
    {
        $perPage = request()->input('per_page', 10);
        $items = UsahaPengajuan::latest()->paginate($perPage);
        return response()->json(['data' => $items], Response::HTTP_OK);
    }

    public function create(UsahaPengajuanRequest $request)
    {
        $usaha = Auth::user()->usaha->first();
        $fields = $request->validated();

        try {
            if ($usaha->saldo < $fields['jumlah_pengajuan']) {
                return response()->json([
                    'message' => 'Saldo tidak mencukupi untuk pengajuan ini.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $fields['usaha_id'] = $usaha->id;

            // Buat pengajuan
            DB::beginTransaction();
            $pengajuan = UsahaPengajuan::create($fields);
            $usaha->saldo -= $fields['jumlah_pengajuan'];
            $usaha->save();
            DB::commit();

            return response()->json(['data' => $pengajuan], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengirim pengajuan: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
