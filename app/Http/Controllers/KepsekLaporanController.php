<?php

namespace App\Http\Controllers;

use App\Models\KantinTransaksi;
use App\Models\LaundryTransaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class KepsekLaporanController extends Controller
{
    public function getUsahaTransaksi()
    {
        $validator = Validator::make(request()->all(), [
            'tahun' => ['nullable', 'integer', 'min:1900', 'max:' . Carbon::now()->year],
            'bulan' => ['nullable', 'integer', 'min:1', 'max:12'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'role' => ['nullable', 'in:Kantin,Laundry'],
            'nama_usaha' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $year = request('tahun', Carbon::now()->year);
        $month = request('bulan', Carbon::now()->month);
        $nama_usaha = request('nama_usaha', null);
        $role = request('role', 'Kantin');
        $perPage = request('per_page', 10);

        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $model = $role == 'Kantin' ? new KantinTransaksi : new LaundryTransaksi;

        try {
            $transaksi = $model->with([
                $role == 'Kantin'
                ? 'kantin_transaksi_detail.kantin_produk'
                : 'laundry_transaksi_detail.laundry_layanan',
                'usaha:id,nama_usaha'
            ])
                ->whereBetween('tanggal_selesai', [$startDate, $endDate])
                ->whereIn('status', ['selesai', 'dibatalkan'])
                ->when($nama_usaha, function ($query) use ($nama_usaha) {
                    $query->whereRelation('usaha', 'nama_usaha', 'like', '%' . $nama_usaha . '%');
                })
                ->paginate($perPage);
    
            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data transaksi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
