<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinPengajuanRequest;
use App\Models\KantinPengajuan;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpFoundation\Response;

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
            $item = KantinPengajuan::create($fields);
            return response()->json(['data' => $item], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal mengirim pengajuan: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
