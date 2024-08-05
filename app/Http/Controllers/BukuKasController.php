<?php

namespace App\Http\Controllers;


use App\Models\PembayaranKategori;
use App\Models\Pengeluaran;
use App\Models\PengeluaranKategori;
use Illuminate\Http\Request;
use App\Models\Pembayaran;


class BukuKasController extends Controller
{
    public function index(){
        $pembayaran = Pembayaran::find(1);
        $kategoriPembayaran = PembayaranKategori::find($pembayaran->pembayaran_kategori_id);
        $pengeluaran = Pengeluaran::find(1);
        $kategoriPengeluaran = PengeluaranKategori::find($pengeluaran->pengeluaran_kategori_id);

        $debet = [
            'nominal' => $pembayaran->nominal(),
            'kategori' => $kategoriPembayaran->nama(),
        ];
        $kredit = [
            'nominal' => $pengeluaran->nominal(),
            'kategori' => $kategoriPengeluaran->nama(),
        ];
        return response()->json([$debet, $kredit]);
    }
}
