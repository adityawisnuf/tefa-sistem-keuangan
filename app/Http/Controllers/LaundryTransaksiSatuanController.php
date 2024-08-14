<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaundryTransaksiSatuanRequest;
use App\Http\Services\StatusTransaksiService;
use App\Models\LaundryItem;
use App\Models\LaundryTransaksiSatuan;
use App\Models\Siswa;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LaundryTransaksiSatuanController extends Controller
{
    protected $statusService;

    public function __construct()
    {
        $this->statusService = new StatusTransaksiService();
    }

    public function index()
    {
        $perPage = request()->input('per_page', 10);
        $transaksi = LaundryTransaksiSatuan::paginate($perPage);
        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function create(LaundryTransaksiSatuanRequest $request)
    {
        $fields = $request->validated();

        // Ambil data layanan laundry terkait
        $layanan = LaundryItem::find($fields['laundry_item_id']);
        $fields['harga'] = $layanan->harga;
        $fields['laundry_id'] = $layanan->laundry_id;
        $fields['harga_total'] = $fields['harga'] * $fields['jumlah_item'];

        try {
            // Ambil data siswa terkait melalui relasi di LaundryTransaksiSatuan
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
            $transaksi = LaundryTransaksiSatuan::create($fields);

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

    public function confirmInitialTransaction(LaundryTransaksiSatuanRequest $request, LaundryTransaksiSatuan $transaksi)
    {
        $fields = $request->validated();

        try {
            // Cek status baru
            if ($fields['status'] === 'dibatalkan') {
                // Kembalikan saldo siswa jika transaksi dibatalkan
                $siswa = Siswa::find($transaksi->siswa_id);
                $siswaWallet = $siswa->siswa_wallet;

                $siswaWallet->nominal += $transaksi->harga_total;
                $siswaWallet->save();
            }

            // Update status transaksi
            $transaksi->update($fields);

            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(LaundryTransaksiSatuan $transaksi)
    {
        $result = $this->statusService->update($transaksi);

        // Set tanggal_selesai only if status is 'selesai'
        if ($result['statusCode'] === Response::HTTP_OK && $transaksi->status === 'selesai') {
            $transaksi->update(['tanggal_selesai' => now()]);
        }

        return response()->json($result['message'], $result['statusCode']);
    }
}
