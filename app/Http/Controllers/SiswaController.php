<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinTransaksiRequest;
use App\Http\Requests\LaundryTransaksiSatuanRequest;
use App\Http\Services\StatusTransaksiService;
use App\Models\KantinProduk;
use App\Models\KantinTransaksi;
use App\Models\LaundryItem;
use App\Models\LaundryTransaksiSatuan;
use App\Models\Siswa;
use illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\LaundryTransaksiKiloanRequest;
use App\Models\LaundryLayanan;
use App\Models\LaundryTransaksiKiloan;

class SiswaController extends Controller
{
    protected $statusService;

    public function __construct()
    {
        $this->statusService = new StatusTransaksiService();
    }

    public function getKantinProduk()
    {
        $siswa = Auth::user()->siswa->first();

        $perPage = request()->input('per_page', 10);
        $transaksi = $siswa->kantin_transaksi()->paginate($perPage);
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function showKantinProduk($id)
    {
        $siswa = Auth::user()->siswa->first();

        $transaksi = KantinTransaksi::where('siswa_id', $siswa->id)
            ->where('id', $id)
            ->first();

        if (!$transaksi) {
            return response()->json([
                'message' => 'Transaksi tidak ditemukan atau tidak memiliki akses ke transaksi ini.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function kantinTransaksi(KantinTransaksiRequest $request)
    {
        $siswa = Auth::user()->siswa->first();
        $fields = $request->validated();

        $siswaWallet = $siswa->siswa_wallet;
        $produk = KantinProduk::find($fields['kantin_produk_id']);

        $fields['siswa_id'] = $siswa->id;
        $fields['kantin_id'] = $produk->kantin_id;
        $fields['harga'] = $produk->harga;
        $fields['harga_total'] = $fields['harga'] * $fields['jumlah'];

        try {
            if ($siswaWallet->nominal < $fields['harga_total']) {
                return response()->json([
                    'message' => 'Saldo tidak mencukupi untuk transaksi ini.',
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($fields['jumlah'] > $produk->stok) {
                return response()->json([
                    'message' => 'Stok tidak mencukupi untuk jumlah yang dipesan.',
                ], Response::HTTP_BAD_REQUEST);
            }

            DB::beginTransaction();
            $transaksi = KantinTransaksi::create($fields);

            $siswaWallet->update([
                'nominal' => $siswaWallet->nominal - $fields['harga_total']
            ]);

            $kantin = $transaksi->kantin;
            $kantin->update([
                'saldo' => $kantin->saldo + $fields['harga_total']
            ]);

            DB::commit();

            return response()->json(['data' => $transaksi], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getLaundrySatuan()
    {
        $siswa = Auth::user()->siswa->first();

        $perPage = request()->input('per_page', 10);
        $transaksi = $siswa->laundry_transaksi_satuan()->paginate($perPage);
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }
    public function getLaundryKiloan()
    {
        $siswa = Auth::user()->siswa->first();

        $perPage = request()->input('per_page', 10);
        $transaksi = $siswa->laundry_transaksi_kiloan()->paginate($perPage);
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function showLaundrySatuan($id)
    {
        $siswa = Auth::user()->siswa->first();

        $transaksi = LaundryTransaksiSatuan::where('siswa_id', $siswa->id)
            ->where('id', $id)
            ->first();

        if (!$transaksi) {
            return response()->json([
                'message' => 'Transaksi tidak ditemukan atau tidak memiliki akses ke transaksi ini.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }
    public function showLaundryKiloan($id)
    {
        $siswa = Auth::user()->siswa->first();

        $transaksi = LaundryTransaksiKiloan::where('siswa_id', $siswa->id)
            ->where('id', $id)
            ->first();

        if (!$transaksi) {
            return response()->json([
                'message' => 'Transaksi tidak ditemukan atau tidak memiliki akses ke transaksi ini.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function createLaundrySatuan(LaundryTransaksiSatuanRequest $request)
    {
        $siswa = Auth::user()->siswa->first();
        $fields = $request->validated();

        $siswaWallet = $siswa->siswa_wallet;
        $layanan = LaundryItem::find($fields['laundry_item_id']);

        $fields['siswa_id'] = $siswa->id;
        $fields['harga'] = $layanan->harga;
        $fields['laundry_id'] = $layanan->laundry_id;
        $fields['harga_total'] = $fields['harga'] * $fields['jumlah_item'];

        try {

            if ($siswaWallet->nominal < $fields['harga_total']) {
                return response()->json([
                    'message' => 'Saldo tidak mencukupi untuk transaksi ini.',
                ], Response::HTTP_BAD_REQUEST);
            }

            DB::beginTransaction();
            $transaksi = LaundryTransaksiSatuan::create($fields);



            $siswaWallet->update([
                'nominal' => $siswaWallet->nominal - $fields['harga_total']
            ]);

            $laundry = $transaksi->laundry;
            $laundry->update([
                'saldo' => $laundry->saldo + $fields['harga_total']
            ]);

            DB::commit();

            return response()->json(['data' => $transaksi], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createLaundryKiloan(LaundryTransaksiKiloanRequest $request)
    {
        $siswa = Auth::user()->siswa->first();
        $fields = $request->validated();

        $layanan = LaundryLayanan::find($fields['laundry_layanan_id']);
        if (!$layanan) {
            return response()->json(['message' => 'Layanan laundry tidak ditemukan.'], Response::HTTP_BAD_REQUEST);
        }

        $fields['siswa_id'] = $siswa->id;
        $fields['harga'] = $layanan->harga_per_kilo;
        $fields['laundry_id'] = $layanan->laundry_id;
        $fields['harga_total'] = $fields['harga'] * $fields['berat'];

        try {
            $siswaWallet = $siswa->siswa_wallet;

            if ($siswaWallet->nominal < $fields['harga_total']) {
                return response()->json([
                    'message' => 'Saldo tidak mencukupi untuk transaksi ini.',
                ], Response::HTTP_BAD_REQUEST);
            }

            DB::beginTransaction();
            $transaksi = LaundryTransaksiKiloan::create($fields);

            $siswaWallet->update([
                'nominal' => $siswaWallet->nominal - $fields['harga_total']
            ]);

            $laundry = $transaksi->laundry;
            $laundry->update([
                'saldo' => $laundry->saldo + $fields['harga_total']
            ]);

            DB::commit();

            return response()->json(['data' => $transaksi], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    public function getLaundryLayanan()
    {
        $perPage = request()->input('per_page', 10);
        $items = LaundryLayanan::latest()->paginate($perPage);
        return response()->json(['data' => $items], Response::HTTP_OK);
    }

    public function getLaundryItem()
    {
        $perPage = request()->input('per_page', 10);
        $items = LaundryItem::latest()->paginate($perPage);
        return response()->json(['data' => $items], Response::HTTP_OK);
    }
}
