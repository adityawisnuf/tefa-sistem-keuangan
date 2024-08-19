<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiswaLaundryRequest;
use App\Models\LaundryItem;
use App\Models\LaundryItemDetail;
use App\Models\LaundryLayanan;
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
    public function getItem()
    {
        $perPage = request()->input('per_page', 10);
        $item = LaundryItem::latest()->paginate($perPage);
        return response()->json(['data' => $item], Response::HTTP_OK);
    }

    public function getItemDetail(LaundryItem $item)
    {
        return response()->json(['data' => $item], Response::HTTP_OK);
    }

    public function createItemTransaksi(SiswaLaundryRequest $request)
    {
        $siswa = Auth::user()->siswa->first();
        $fields = $request->validated();

        try {
            $siswaWallet = $siswa->siswa_wallet;

            $fields['siswa_id'] = $siswa->id;
            $fields['laundry_id'] = LaundryItem::find($fields['item_detail'][0]['laundry_item_id'])->laundry_id ?? null;
            $fields['jumlah_item'] = count($fields['item_detail']);
            $fields['harga_total'] = 0;

            DB::beginTransaction();

            $transaksiSatuan = LaundryTransaksiSatuan::create($fields);

            foreach ($fields['item_detail'] as $itemData) {
                $item = LaundryItem::find($itemData['laundry_item_id']);
                $jumlah = $itemData['jumlah'];
                $harga_total = $item->harga * $jumlah;

                LaundryItemDetail::create([
                    'laundry_item_id' => $item->id,
                    'laundry_transaksi_satuan_id' => $transaksiSatuan->id,
                    'jumlah' => $jumlah,
                    'harga' => $item->harga,
                    'harga_total' => $harga_total,
                ]);

                $fields['harga_total'] += $harga_total;
            }

            if ($siswaWallet->nominal < $fields['harga_total']) {
                return response()->json(['message' => 'Saldo tidak mencukupi untuk transaksi ini.'], Response::HTTP_BAD_REQUEST);
            }

            DB::commit();

            return response()->json(['data' => $transaksiSatuan], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getItemRiwayat()
    {
        $siswa = Auth::user()->siswa()->first();
        $perPage = request()->input('per_page', 10);
        $riwayat = $siswa->laundry_transaksi_satuan()->paginate($perPage);
        return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }
    public function getLayanan()
    {
        $perPage = request()->input('per_page', 10);
        $item = LaundryLayanan::latest()->paginate($perPage);
        return response()->json(['data' => $item], Response::HTTP_OK);
    }

    public function getLayananDetail(LaundryLayanan $layanan)
    {
        return response()->json(['data' => $layanan], Response::HTTP_OK);
    }

    public function createLayananTransaksi(SiswaLaundryRequest $request, LaundryLayanan $layanan)
    {
        $siswa = Auth::user()->siswa->first();
        $fields = $request->validated();

        try {
            $siswaWallet = $siswa->siswa_wallet;

            $fields['siswa_id'] = $siswa->id;
            $fields['laundry_id'] = $layanan->laundry_id;
            $fields['laundry_layanan_id'] = $layanan->id;
            $fields['harga'] = $layanan->harga_per_kilo;
            $fields['harga_total'] = $fields['harga'] * $fields['berat'];

            if ($siswaWallet->nominal < $fields['harga_total']) {
                return response()->json([
                    'message' => 'Saldo tidak mencukupi untuk transaksi ini.',
                ], Response::HTTP_BAD_REQUEST);
            }

            DB::beginTransaction();

            $transaksi = LaundryTransaksiKiloan::create($fields);
            $laundry = $transaksi->laundry;

            $siswaWallet->update([
                'nominal' => $siswaWallet->nominal - $fields['harga_total']
            ]);

            $laundry->update([
                'saldo' => $laundry->saldo + $fields['harga_total']
            ]);

            SiswaWalletRiwayat::create([
                'siswa_wallet_id' => $siswaWallet->id,
                'merchant_order_id' => null,
                'tipe_transaksi' => 'pengeluaran',
                'nominal' => $fields['harga_total']
            ]);

            DB::commit();

            return response()->json(['data' => $transaksi], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getLayananRiwayat()
    {
        $siswa = Auth::user()->siswa()->first();
        $perPage = request()->input('per_page', 10);
        $riwayat = $siswa->laundry_transaksi_satuan()->paginate($perPage);
        return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }
}
