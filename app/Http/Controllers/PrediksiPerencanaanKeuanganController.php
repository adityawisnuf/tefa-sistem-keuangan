<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PrediksiPerencanaanKeuanganController extends Controller
{
    public function index()
    {
        $data = $this->retrieveData();

        return response()->json(['data' => $data], 200);
    }

    private function retrieveData()
    {
        // Retrieve all data from the database
        $anggaran = Anggaran::all();

        // Remove the 'id' field from the result
        $anggaranFiltered = $anggaran->map(function ($item) {
            $item->tanggal_pengajuan = Carbon::parse($item->tanggal_pengajuan)->format('d M Y');
            $item->target_terealisasikan = $item->target_terealisasikan
                ? Carbon::parse($item->target_terealisasikan)->format('d M Y')
                : null;

            return $item->makeHidden(['id', 'deskripsi', 'created_at', 'updated_at']);
        });

        return [
            'anggaran' => $anggaranFiltered,
            'total_anggaran_diajukan' => $this->formatToRupiah($anggaran->where('status', 1)->sum('nominal')),
            'total_anggaran_diapprove' => $this->formatToRupiah($anggaran->where('status', 2)->sum('nominal')),
            'total_anggaran_terealisasikan' => $this->formatToRupiah($anggaran->where('status', 3)->sum('nominal')),
            'total_anggaran_gagal' => $this->formatToRupiah($anggaran->where('status', 4)->sum('nominal')),
            'count_diajukan' => $anggaran->where('status', 1)->count(),
            'count_diapprove' => $anggaran->where('status', 2)->count(),
            'count_terealisasikan' => $anggaran->where('status', 3)->count(),
            'count_gagal' => $anggaran->where('status', 4)->count(),
        ];
    }

    private function formatToRupiah($value)
    {
        // Memformat nilai ke dalam format Rupiah
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
