<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OrangTuaRiwayatController extends Controller
{
    public function getRiwayatSiswa()
    {
        $orangTua = Auth::user()->orangtua()->with('siswa')->firstOrFail();
        $siswaId = request('siswa_id', null);
        $perPage = request('per_page', 10);
        $role = request('role', 'Kantin');

        $siswa = $orangTua->siswa()->find($siswaId) ?? $orangTua->siswa->first();

        $riwayat = $role == 'Kantin'
            ? $siswa->kantin_transaksi()
                ->with('kantin_transaksi_detail.kantin_produk:id,nama_produk,foto_produk,harga_jual')
                ->whereIn('status', ['dibatalkan', 'selesai'])
                ->paginate($perPage)
            : $siswa->laundry_transaksi()
                ->with('laundry_transaksi_detail.laundry_layanan:id,nama_layanan,foto_layanan,harga')
                ->whereIn('status', ['dibatalkan', 'selesai'])
                ->paginate($perPage);

        return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }
}
