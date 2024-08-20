<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryTransaksiKiloanRequest;
use App\Http\Requests\LaundryTransaksiRequest;
use App\Http\Services\StatusTransaksiService;
use App\Models\LaundryLayanan;
use App\Models\LaundryTransaksi;
use App\Models\LaundryTransaksiKiloan;
use App\Models\Siswa;
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
        $transaksi = $usaha->laundry_transaksi()->where('status', ['pending', 'proses', 'siap_diambil'])->paginate($perPage);

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }
    //show done
    public function showLaundryKiloan($id)
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
        $result = $this->statusService->update($transaksi);
        if ($result['statusCode'] === Response::HTTP_OK && $transaksi->status === 'selesai') {
            $transaksi->update(['tanggal_selesai' => now()]);
        }
        return response()->json($result['message'], $result['statusCode']);
    }

    public function confirmInitialTransaction(LaundryTransaksiRequest $request, LaundryTransaksi $transaksi)
    {
        $fields = $request->validated();

        $siswaWallet = $transaksi->siswa->siswa_wallet;
        $usaha = $transaksi->usaha;

        $result = $this->statusService->confirmInitialTransaction($fields, $transaksi);

        DB::beginTransaction();
        if ($result['statusCode'] === Response::HTTP_OK || $transaksi->status === 'dibatalkan') {
            $transaksi->update([
                'tanggal_selesai' => now()
            ]);
            $siswaWallet->update([
                'nominal' => $siswaWallet->nominal + $transaksi->harga_total
            ]);
            $usaha->update([
                'saldo' => $usaha->saldo - $transaksi->harga_total
            ]);
        }
        DB::commit();

        return response()->json($result['message'], $result['statusCode']);
    }

}
