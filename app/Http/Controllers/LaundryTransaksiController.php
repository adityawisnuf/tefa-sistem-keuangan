<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryTransaksiKiloanRequest;
use App\Http\Requests\LaundryTransaksiRequest;
use App\Http\Services\StatusTransaksiService;
use App\Models\LaundryLayanan;
use App\Models\LaundryTransaksi;
use App\Models\LaundryTransaksiKiloan;
use App\Models\Siswa;
use App\Models\SiswaWalletRiwayat;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class LaundryTransaksiController extends Controller
{
    protected $statusService;

    public function __construct()
    {
        $this->statusService = new StatusTransaksiService();
    }
    //get done
    public function getActiveTransaction()
    {
        $usaha = Auth::user()->usaha->firstOrFail();

        $perPage = request()->input('per_page', 10);
        $transaksi = $usaha->laundry_transaksi()
            ->with(['siswa:id,nama_depan,nama_belakang', 'laundry_transaksi_detail.laundry_layanan'])
            ->withSum('laundry_transaksi_detail as harga_total', 'harga', 'total_harga') // Tambahkan baris ini
            ->whereIn('status', ['pending', 'proses', 'siap_diambil'])
            ->paginate($perPage);

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function getCompletedTransaction()
    {
        $usaha = Auth::user()->usaha->firstOrFail();

        $perPage = request()->input('per_page', 10);
        $transaksi = $usaha->laundry_transaksi()->whereIn('status', ['selesai', 'dibatalkan'])->paginate($perPage);

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }
    //show done
    public function showLaundry($id)
    {
        $usaha = Auth::user()->usaha->first();

        $transaksi = LaundryTransaksi::where('usaha_id', $usaha->id)
            ->where('id', $id)
            ->first();

        if (!$transaksi) {
            return response()->json([
                'message' => 'Transaksi tidak ditemukan atau tidak memiliki akses ke transaksi ini.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function update(LaundryTransaksi $transaksi)
    {
        $this->statusService->update($transaksi);
        if ($transaksi->status === 'selesai') {
            $transaksi->update(['tanggal_selesai' => now()]);
        }
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function confirmInitialTransaction(LaundryTransaksiRequest $request, LaundryTransaksi $transaksi)
    {
        $fields = $request->validated();

        $siswaWallet = $transaksi->siswa->siswa_wallet;
        $usaha = $transaksi->usaha;

        DB::beginTransaction();
        $this->statusService->confirmInitialTransaction($fields, $transaksi);
        if ($transaksi->status === 'dibatalkan') {
            $harga_total = $transaksi->laundry_transaksi_detail->sum(function ($detail) {
                return $detail->harga * $detail->jumlah;
            });

            $transaksi->update([
                'tanggal_selesai' => now()
            ]);

            $usaha->update([
                'saldo' => $usaha->saldo - $harga_total
            ]);

            $siswaWallet->update([
                'nominal' => $siswaWallet->nominal + $harga_total
            ]);

            SiswaWalletRiwayat::create([
                'siswa_wallet_id' => $siswaWallet->id,
                'merchant_order_id' => null,
                'tipe_transaksi' => 'pemasukan',
                'nominal' => $harga_total,
            ]);
        }
        DB::commit();

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

}
