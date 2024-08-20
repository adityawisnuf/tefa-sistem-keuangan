<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiswaKantinRequest;
use App\Models\KantinProduk;
use App\Models\KantinTransaksi;
use App\Models\KantinTransaksiDetail;
use App\Models\SiswaWalletRiwayat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SiswaKantinController extends Controller
{
    public function getProduk()
    {
        $kategori = request()->input('kategori');
        $perPage = request()->input('per_page', 10);

        $produk = $kategori
            ? KantinProduk::where('kantin_produk_kategori_id', $kategori)->paginate($perPage)
            : KantinProduk::paginate($perPage);

        return response()->json(['data' => $produk], Response::HTTP_OK);
    }

    public function getProdukDetail(KantinProduk $produk)
    {
        return response()->json(['data' => $produk], Response::HTTP_OK);
    }

    public function createProdukTransaksi(SiswaKantinRequest $request, KantinProduk $produk)
    {
        $siswa = Auth::user()->siswa()->with('siswa_wallet')->firstOrFail();
        $fields = $request->validated();
        
        $usaha = KantinProduk::find($fields['detail_pesanan'][0]['kantin_produk_id'])->usaha;
        $siswaWallet = $siswa->siswa_wallet;
        
        $fields['siswa_id'] = $siswa->id;
        $fields['usaha_id'] = $usaha->id;

        DB::beginTransaction();
        $kantinTransaksi = KantinTransaksi::create($fields);
        $totalHarga = 0;

        foreach ($fields['detail_pesanan'] as $productDetail) {
            $product = $usaha->kantin_produk()->findOrFail($productDetail['kantin_produk_id']);
            $qty = $productDetail['jumlah'];

            if ($product->stok < $qty) {
                return response()->json(['message' => 'Stok produk {$product->nama_produk} tidak mencukupi.'], Response::HTTP_BAD_REQUEST);
            }

            $product->update([
                'stok' => $product->stok - $qty
            ]);
            
            KantinTransaksiDetail::create([
                'kantin_produk_id' => $product->id,
                'kantin_transaksi_id' => $kantinTransaksi->id,
                'jumlah' => $qty,
                'harga' => $product->harga_jual,
            ]);

            $totalHarga += $product->harga_jual * $productDetail['jumlah'];
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
        
        return response()->json(['data' => $kantinTransaksi], Response::HTTP_CREATED);
    }

    public function getKantinRiwayat()
    {
        $siswa = Auth::user()->siswa->firstOrFail();
        $perPage = request()->input('per_page', 10);

        $riwayat = $siswa->kantin_transaksi()->with('kantin_transaksi_detail.kantin_produk')->latest()->paginate($perPage);
        return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }
}

