<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryLayananRequest;
use App\Http\Requests\SiswaLaundryRequest;
use App\Models\LaundryItem;
use App\Models\LaundryItemDetail;
use App\Models\LaundryLayanan;
use App\Models\LaundryTransaksi;
use App\Models\LaundryTransaksiDetail;
use App\Models\LaundryTransaksiKiloan;
use App\Models\LaundryTransaksiSatuan;
use App\Models\SiswaWalletRiwayat;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SiswaLaundryController extends Controller
{
    public function getLaundryLayanan()
    {
        $perPage = request()->input('per_page', 10);
        $items = LaundryLayanan::latest()->paginate($perPage);
        return response()->json(['data' => $items], Response::HTTP_OK);
    }
    public function getLayananDetail(LaundryLayanan $layanan)
    {
        return response()->json(['data' => $layanan], Response::HTTP_OK);
    }

    public function getLayananRiwayat()
    {
        $siswa = Auth::user()->siswa()->first();
        $perPage = request()->input('per_page', 10);
        $riwayat = $siswa->laundry_transaksi()->paginate($perPage);
        return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }

    public function createLayananTransaksi(SiswaLaundryRequest $request, LaundryLayanan $layanan)
    {
        $siswa = Auth::user()->siswa()->with('siswa_wallet')->firstOrFail();
        $fields = $request->validated();

        $usaha = LaundryLayanan::find($fields['detail_pesanan'][0]['laundry_layanan_id'])->usaha;
        $siswaWallet = $siswa->siswa_wallet;

        $fields['siswa_id'] = $siswa->id;
        $fields['usaha_id'] = $usaha->id;

        DB::beginTransaction();
        $laundryTransaksi = LaundryTransaksi::create($fields);
        $totalHarga = 0;

        foreach ($fields['detail_pesanan'] as $layananDetail) {
            $layanan = $usaha->laundry_layanan()->findOrFail($layananDetail['laundry_layanan_id']);
            $qty = $layananDetail['jumlah'];


            LaundryTransaksiDetail::create([
                'laundry_layanan_id' => $layanan->id,
                'laundry_transaksi_id' => $laundryTransaksi->id,
                'jumlah' => $qty,
                'harga' => $layanan->harga
            ]);

            $totalHarga += $layanan->harga * $layananDetail['jumlah'];
        }


        if ($siswaWallet->nominal < $totalHarga) {
            return response()->json(['message' => 'Saldo tidak mencukupi untuk transaksi ini.'], Response::HTTP_BAD_REQUEST);
        }

        $usaha->update([
            'saldo' => $usaha->saldo + $totalHarga,
        ]);

        $siswaWallet->update([
            'nominal' => $siswaWallet->nominal - $totalHarga,
        ]);

        SiswaWalletRiwayat::create([
            'siswa_wallet_id' => $siswaWallet->id,
            'merchant_order_id' => null,
            'tipe_transaksi' => 'pengeluaran',
            'nominal' => $totalHarga,
        ]);
        DB::commit();

        return response()->json(['data' => $laundryTransaksi], Response::HTTP_CREATED);
    }

}
