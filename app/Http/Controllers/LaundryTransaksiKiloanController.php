<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryTransaksiKiloanRequest;
use App\Http\Services\StatusTransaksiService;
use App\Models\LaundryLayanan;
use App\Models\LaundryTransaksiKiloan;
use App\Models\Siswa;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class LaundryTransaksiKiloanController extends Controller
{
    protected $statusService;

    public function __construct()
    {
        $this->statusService = new StatusTransaksiService();
    }

    public function index()
    {
        $perPage = request()->input('per_page', 10);
        $transaksi = LaundryTransaksiKiloan::paginate($perPage);
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function create(LaundryTransaksiKiloanRequest $request)
    {
        $fields = $request->validated();

        $layanan = LaundryLayanan::find($fields['laundry_layanan_id']);
        if (!$layanan) {
            return response()->json(['message' => 'Layanan laundry tidak ditemukan.'], Response::HTTP_BAD_REQUEST);
        }
        $fields['harga'] = $layanan->harga_per_kilo;
        $fields['laundry_id'] = $layanan->laundry_id;
        $fields['harga_total'] = $fields['harga'] * $fields['berat'];

        try {
            // Ambil data siswa terkait melalui relasi di LaundryTransaksiKiloan
            $siswa = Siswa::find($fields['siswa_id']);
            $siswaWallet = $siswa->siswa_wallet;

            // Validasi apakah saldo siswa mencukupi
            if ($siswaWallet->nominal < $fields['harga_total']) {
                return response()->json([
                    'message' => 'Saldo tidak mencukupi untuk transaksi ini.',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Buat transaksi
            $transaksi = LaundryTransaksiKiloan::create($fields);

            // Kurangi saldo siswa
            $siswaWallet->nominal -= $fields['harga_total'];
            $siswaWallet->save();

            return response()->json(['data' => $transaksi], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(LaundryTransaksiKiloan $transaksi)
    {
        $result = $this->statusService->update($transaksi);
        return response()->json($result['message'], $result['statusCode']);
    }

    public function confirmInitialTransaction(LaundryTransaksiKiloanRequest $request, LaundryTransaksiKiloan $transaksi)
    {
        $fields = $request->validated();

        try {
            // Cek status baru
            if ($fields['status'] === 'dibatalkan') {
                // Kembalikan saldo siswa jika transaksi dibatalkan
                $siswa = Siswa::find($transaksi->siswa_id);
                $siswaWallet = $siswa->siswa_wallet;

                // Tambahkan kembali saldo yang telah dikurangkan
                $siswaWallet->nominal += $transaksi->harga_total;
                $siswaWallet->save();
            } elseif ($fields['status'] === 'proses') {
                // Tidak ada perubahan pada saldo jika status diubah menjadi 'proses'
                // Implementasikan logika tambahan jika diperlukan
            }

            // Update status transaksi
            $transaksi->update($fields);

            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
