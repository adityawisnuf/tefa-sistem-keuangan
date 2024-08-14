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
            DB::beginTransaction();
            $transaksi = LaundryTransaksiKiloan::create($fields);

            // Kurangi saldo siswa
            $siswaWallet->nominal -= $fields['harga_total'];
            $siswaWallet->save();
            DB::commit();

            return response()->json(['data' => $transaksi], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(LaundryTransaksiKiloan $transaksi)
    {
        $result = $this->statusService->update($transaksi);

        // Set tanggal_selesai only if status is 'selesai'
        if ($result['statusCode'] === Response::HTTP_OK && $transaksi->status === 'selesai') {
            $transaksi->update(['tanggal_selesai' => now()]);
        }

        return response()->json($result['message'], $result['statusCode']);
    }

    public function confirmInitialTransaction(LaundryTransaksiKiloanRequest $request, LaundryTransaksiKiloan $transaksi)
    {
        $fields = $request->validated();

        // Ambil data siswa terkait melalui relasi di LaundryTransaksiKiloan
        $siswaWallet = $transaksi->siswa->siswa_wallet;

        try {
            // Handle perubahan status
            switch ($fields['status']) {
                case 'proses':
                    // Update status ke 'proses'
                    $transaksi->update([
                        'status' => 'proses',
                    ]);
                    return response()->json([
                        'message' => 'Transaksi dalam proses.',
                        'data' => $transaksi,
                    ], Response::HTTP_OK);

                case 'dibatalkan':
                    // Kembalikan saldo siswa
                    $siswaWallet->nominal += $transaksi->harga_total;
                    $siswaWallet->save();

                    // Update status ke 'dibatalkan'
                    $transaksi->update([
                        'status' => 'dibatalkan',
                        'tanggal_selesai' => now(), // atau bisa disesuaikan dengan logika bisnis
                    ]);
                    return response()->json([
                        'message' => 'Transaksi telah dibatalkan dan saldo dikembalikan.',
                        'data' => $transaksi,
                    ], Response::HTTP_OK);

                default:
                    return response()->json([
                        'message' => 'Status tidak valid.',
                    ], Response::HTTP_UNAUTHORIZED);
            }
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal mengubah status transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
