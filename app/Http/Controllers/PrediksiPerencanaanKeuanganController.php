<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
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
            return $item->makeHidden('id', 'deskripsi', 'created_at', 'updated_at');
        });

        return [
            'anggaran' => $anggaranFiltered,
            'count_diajukan' => $anggaran->where('status',1)->count(),
            'count_diapprove' => $anggaran->where('status',2)->count(),
            'count_terealisasikan' => $anggaran->where('status',3)->count(),
            'count_gagal' => $anggaran->where('status',4)->count(),
        ];
    }
}
