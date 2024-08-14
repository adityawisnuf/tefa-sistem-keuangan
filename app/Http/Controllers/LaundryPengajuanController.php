<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryPengajuanRequest;
use App\Models\LaundryPengajuan;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
class LaundryPengajuanController extends Controller
{
    public function index()
    {
        $laundry = Auth::user()->laundry->first();

        $perPage = request()->input('per_page', 10);
        $pengajuan = $laundry->laundry_pengajuan->latest()->paginate($perPage);
        return response()->json(['data' => $pengajuan], Response::HTTP_OK);
    }

    public function create(LaundryPengajuanRequest $request)
    {
        $laundry = Auth::user()->laundry->first();
        $fields = $request->validated();
        
        try {
            $fields['laundry_id'] = $laundry->id;
            $pengajuan = LaundryPengajuan::create($fields);
            return response()->json(['data' => $pengajuan], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal mengirim pengajuan: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
