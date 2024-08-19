<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinTransaksiRequest;
use App\Http\Services\StatusTransaksiService;
use App\Models\KantinProduk;
use App\Models\KantinTransaksi;
use illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class KantinTransaksiController extends Controller
{
    protected $statusService;

    public function __construct()
    {
        $this->statusService = new StatusTransaksiService();
    }

    public function getActiveTransaction()
    {
        $usaha = Auth::user()->usaha->first();

        $perPage = request()->input('per_page', 10);
        $transaksi = $usaha->kantin_transaksi()->where('status', ['pending', 'proses', 'siap_diambil'])->paginate($perPage);
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function update(KantinTransaksi $transaksi)
    {
        $result = $this->statusService->update($transaksi);
        if ($result['statusCode'] === Response::HTTP_OK && $transaksi->status === 'selesai') {
            $transaksi->update(['tanggal_selesai' => now()]);
        }
        return response()->json($result['message'], $result['statusCode']);
    }

    public function confirmInitialTransaction(KantinTransaksiRequest $request, KantinTransaksi $transaksi)
    {
        $fields = $request->validated();
        $siswaWallet = $transaksi->siswa->siswa_wallet;
        $kantin = $transaksi->kantin;

        try {
            $result = $this->statusService->confirmInitialTransaction($fields, $transaksi);

            DB::beginTransaction();
            if ($result['statusCode'] === Response::HTTP_OK) {
                if ($transaksi->status === 'proses') {
                    $kantinProduk = $transaksi->kantin_produk;
                    $kantinProduk->update([
                        'stok' => $kantinProduk->stok - $transaksi->jumlah
                    ]);
                } elseif ($transaksi->status === 'dibatalkan') {
                    $transaksi->update([
                        'tanggal_selesai' => now()
                    ]);
                    $siswaWallet->update([
                        'nominal' => $siswaWallet->nominal + $transaksi->harga_total
                    ]);
                    $kantin->update([
                        'saldo' => $kantin->saldo - $transaksi->harga_total
                    ]);
                }
            }
            DB::commit();

            return response()->json($result['message'], $result['statusCode']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengubah status transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
