<?php

namespace App\Http\Controllers;

use App\Models\KantinTransaksi;
use App\Models\LaundryTransaksi;
use App\Models\UsahaPengajuan;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class KepsekController extends Controller
{
    private $startOfMonth;
    private $endOfMonth;

    public function __construct()
    {
        $this->startOfMonth = now()->startOfMonth();
        $this->endOfMonth = now()->endOfMonth();
    }


    public function getUsahaTransaksi()
    {

        $validator = Validator::make(request()->all(), [
            'per_page' => ['nullable', 'integer', 'min:1'],
            'role' => ['nullable', 'in:Kantin,Laundry'],
            'nama_usaha' => ['nullable', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $perPage = request('perpage', 10);
        $role = request('role', 'Kantin');
        $namaUsaha = request('nama_usaha');

        try {
            $model = $role == 'Kantin' ? new KantinTransaksi : new LaundryTransaksi;

            $transaksi = $model->with([$role == 'Kantin' ? 'kantin_transaksi_detail' : 'laundry_transaksi_detail', 'usaha:id,nama_usaha'])
                ->whereIn('status', ['dibatalkan', 'selesai'])
                ->whereBetween('tanggal_selesai', [$this->startOfMonth, $this->endOfMonth])
                ->when($namaUsaha, function ($query) use ($namaUsaha) {
                    $query->whereRelation("usaha", 'nama_usaha', 'like', '%' . $namaUsaha . '%');
                })
                ->paginate($perPage);

            return response()->json(["data" => $transaksi], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('getUsahaTransaksi: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan ketika menampilkan data Usaha Transaksi'], RESPONSE::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function getUsahaPengajuan()
    {

        $validator = Validator::make(request()->all(), [
            'per_page' => ['nullable', 'integer', 'min:1'],
            'nama_usaha' => ['nullable', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $perPage = request()->input('per_page', 10);
        $namaUsaha = request('nama_usaha');

        try {
            $pengajuan = UsahaPengajuan::with('usaha:id,nama_usaha')
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->when($namaUsaha, function ($query) use ($namaUsaha) {
                $query->whereRelation("usaha", 'nama_usaha', 'like', '%' . $namaUsaha . '%');
            })
            ->paginate($perPage);

            return response()->json(['data' => $pengajuan], Response::HTTP_OK);
        } catch(Exception $e){
            Log::error('getUsahaPengajuan: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan ketika menampilkan data Usaha Pengajuan'], RESPONSE::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}