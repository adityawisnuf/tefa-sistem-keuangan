<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryTransaksiKiloanRequest;
use App\Http\Services\StatusTransaksiService;
use App\Models\LaundryLayanan;
use App\Models\LaundryTransaksiKiloan;
use App\Models\Siswa;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class LaundryTransaksiKiloanController extends Controller
{
    protected $statusService;

    public function __construct()
    {
        $this->statusService = new StatusTransaksiService();
    }

    public function index()
    {
        $laundry = Auth::user()->laundry->first();

        $perPage = request()->input('per_page', 10);
        $transaksi = $laundry->laundry_transaksi_kiloan()->paginate($perPage);
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function showLaundryKiloan($id)
    {
        $laundry = Auth::user()->laundry->first();

        $transaksi = LaundryTransaksiKiloan::where('laundry_id', $laundry->id)
            ->where('id', $id)
            ->first();

        if (!$transaksi) {
            return response()->json([
                'message' => 'Transaksi tidak ditemukan atau tidak memiliki akses ke transaksi ini.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function update(LaundryTransaksiKiloan $transaksi)
    {
        $result = $this->statusService->update($transaksi);
        if ($result['statusCode'] === Response::HTTP_OK && $transaksi->status === 'selesai') {
            $transaksi->update(['tanggal_selesai' => now()]);
        }
        return response()->json($result['message'], $result['statusCode']);
    }

    public function confirmInitialTransaction(LaundryTransaksiKiloanRequest $request, LaundryTransaksiKiloan $transaksi)
    {
        $fields = $request->validated();
        $siswaWallet = $transaksi->siswa->siswa_wallet;
        $laundry = $transaksi->laundry;

        try {
            DB::beginTransaction();

            if ($fields['status'] === 'proses') {
                $transaksi->update(['status' => 'proses']);
            } elseif ($fields['status'] === 'dibatalkan') {
                $transaksi->update([
                    'status' => 'dibatalkan',
                    'tanggal_selesai' => now(),
                ]);

                $siswaWallet->update(['nominal' => $siswaWallet->nominal + $transaksi->harga_total]);
                $laundry->update(['saldo' => $laundry->saldo - $transaksi->harga_total]);
            } else {
                return response()->json(['message' => 'Status tidak valid.'], Response::HTTP_BAD_REQUEST);
            }

            DB::commit();

            return response()->json([
                'message' => 'Transaksi berhasil diperbarui.',
                'data' => $transaksi,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengubah status transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
