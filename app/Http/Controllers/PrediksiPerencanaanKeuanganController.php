<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use Illuminate\Http\Request;

class PrediksiPerencanaanKeuanganController extends Controller
{
    public function index()
    {
        $data = $this->retrieveData();

        return response()->json([
            'data' => $data,
        ], 200);
    }

    private function retrieveData()
    {
        // Retrieve data from the database using eager loading for efficiency
        $anggaran = Anggaran::get();

        // Group the data based on status
        $data = [
            'diajukan' => $anggaran->where('status', 1),
            'diapprove' => $anggaran->where('status', 2),
            'terealisasikan' => $anggaran->where('status', 3),
            'gagal' => $anggaran->where('status', 4),
        ];

        return $data;
    }
}
