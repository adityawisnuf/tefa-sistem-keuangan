<?php

namespace App\Http\Controllers;

use App\Http\Requests\KantinTransaksiRequest;
use App\Http\Services\StatusTransaksiService;
use App\Models\KantinProduk;
use App\Models\KantinTransaksi;
use App\Models\Siswa;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class KantinTransaksiController extends Controller
{
    protected $statusService;

    public function __construct()
    {
        $this->statusService = new StatusTransaksiService();
    }

    public function index()
    {
        $perPage = request()->input('per_page', 10);
        $transaksi = KantinTransaksi::paginate($perPage);
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function create(KantinTransaksiRequest $request)
    {
        $fields = $request->validated();

        // Ambil data produk terkait
        $produk = KantinProduk::find($fields['kantin_produk_id']);
        $fields['harga'] = $produk->harga;
        $fields['kantin_id'] = $produk->kantin_id;
        $fields['harga_total'] = $fields['harga'] * $fields['jumlah'];

        try {
            // Ambil data siswa terkait melalui relasi di KantinTransaksi
            $siswa = Siswa::find($fields['siswa_id']);
            $siswaWallet = $siswa->siswa_wallet;

            // Validasi apakah saldo siswa mencukupi
            if ($siswaWallet->nominal < $fields['harga_total']) {
                return response()->json([
                    'message' => 'Saldo tidak mencukupi untuk transaksi ini.',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Buat transaksi
            $transaksi = KantinTransaksi::create($fields);

            // Kurangi saldo siswa
            $siswaWallet->nominal -= $fields['harga_total'];
            $siswaWallet->save();

            return response()->json(['data' => $transaksi], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function update(KantinTransaksi $transaksi)
    {
        $result = $this->statusService->update($transaksi);
        return response()->json($result['message'], $result['statusCode']);
    }

    public function confirmInitialTransaction(KantinTransaksiRequest $request, KantinTransaksi $transaksi)
    {
        $fields = $request->validated();

        // Ambil data siswa terkait melalui relasi di KantinTransaksi
        $siswaWallet = $transaksi->siswa->siswa_wallet;

        try {
            // Handle perubahan status
            switch ($fields['status']) {
                case 'proses':
                    // Update status ke 'proses'
                    $transaksi->update([
                        'status' => 'proses',
                        'tanggal_selesai' => now(), // atau bisa disesuaikan dengan logika bisnis
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
