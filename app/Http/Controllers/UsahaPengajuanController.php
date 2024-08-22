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

        $items = $usaha->usaha_pengajuan()->paginate($perPage);
        return response()->json(['data' => $items], Response::HTTP_OK);
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
        $fields['nama_usaha'] = $usaha->nama_usaha;

        DB::beginTransaction();
        $pengajuan = UsahaPengajuan::create($fields);
        $usaha->update([
            'saldo' => $usaha->saldo - $fields['jumlah_pengajuan']
        ]);
        DB::commit();

        return response()->json(['data' => [array_merge(['nama_usaha' => $usaha->nama_usaha], $pengajuan->toArray())]], Response::HTTP_CREATED);
    }
}
