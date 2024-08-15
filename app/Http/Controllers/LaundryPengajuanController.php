<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryPengajuanRequest;
use App\Models\LaundryPengajuan;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
class LaundryPengajuanController extends Controller
{
    public function index()
    {
        $laundry = Auth::user()->laundry->first();

        $perPage = request()->input('per_page', 10);
        $pengajuan = $laundry->laundry_pengajuan()->latest()->paginate($perPage);
        return response()->json(['data' => $pengajuan], Response::HTTP_OK);
    }

    public function create(LaundryPengajuanRequest $request)
    {
        $laundry = Auth::user()->laundry->first();
        $fields = $request->validated();

        try {
            $laundry = Auth::user()->laundry->first();

            if ($laundry->saldo < $fields['jumlah_pengajuan']) {
                return response()->json([
                    'message' => 'Saldo tidak mencukupi untuk pengajuan ini.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $fields['laundry_id'] = $laundry->id;

            // Buat pengajuan
            DB::beginTransaction();

            $item = LaundryPengajuan::create($fields);

            // Kurangi saldo jika pengajuan berhasil dibuat
            $laundry->saldo -= $fields['jumlah_pengajuan'];
            $laundry->save();
            DB::commit();

            return response()->json(['data' => $item], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengirim pengajuan: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
