<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PembayaranSiswaResource;
use App\Models\PembayaranSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PembayaranSiswaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $query = PembayaranSiswa::oldest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nominal', 'like', "%$search%")
                    ->orWhere('merchant_order_id', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%");
            });
        }

        $pembayaranSiswa = $request->input('page') === 'all' ? $query->get() : $query->paginate(5);

        return new PembayaranSiswaResource(true, 'List Pembayaran Siswa', $pembayaranSiswa);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'siswa_id' => 'required|exists:siswa,id',
            'pembayaran_id' => 'required|exists:pembayaran,id',
            'nominal' => 'required|numeric',
            'merchant_order_id' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Generate a unique merchant_order_id if not provided
        $merchantOrderId = $request->input('merchant_order_id') ?: 'ORDER-'.time().'-'.strtoupper(uniqid());

        $pembayaranSiswa = PembayaranSiswa::create(array_merge(
            $validator->validated(),
            ['merchant_order_id' => $merchantOrderId]
        ));

        return new PembayaranSiswaResource(true, 'Pembayaran Siswa Berhasil Ditambahkan!', $pembayaranSiswa);
    }

    public function show($id)
    {
        $pembayaranSiswa = PembayaranSiswa::find($id);

        return new PembayaranSiswaResource(true, 'Detail Pembayaran Siswa!', $pembayaranSiswa);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'siswa_id' => 'required|exists:siswa,id',
            'pembayaran_id' => 'required|exists:pembayaran,id',
            'nominal' => 'required|numeric',
            'merchant_order_id' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pembayaranSiswa = PembayaranSiswa::find($id);
        $pembayaranSiswa->update($validator->validated());

        return new PembayaranSiswaResource(true, 'Pembayaran Siswa Berhasil Diubah!', $pembayaranSiswa);
    }

    public function destroy($id)
    {
        $pembayaranSiswa = PembayaranSiswa::find($id);
        $pembayaranSiswa->delete();

        return new PembayaranSiswaResource(true, 'Pembayaran Siswa Berhasil Dihapus!', null);
    }

    // Metode untuk melakukan pembayaran
    public function bayar(Request $request, $id)
    {
        $pembayaranSiswa = PembayaranSiswa::find($id);

        if (! $pembayaranSiswa) {
            return response()->json(['error' => 'Pembayaran Siswa tidak ditemukan'], 404);
        }

        // Validasi dan proses pembayaran
        $validator = Validator::make($request->all(), [
            'nominal' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Update status atau lakukan proses pembayaran lainnya
        $pembayaranSiswa->status = 1; // Misalnya, set status ke "dibayar"
        $pembayaranSiswa->save();

        return new PembayaranSiswaResource(true, 'Pembayaran Berhasil!', $pembayaranSiswa);
    }

    // Metode untuk riwayat pembayaran
    public function riwayatPembayaran(Request $request)
    {
        $siswaId = $request->user()->id;
        $pembayaranSiswa = PembayaranSiswa::where('siswa_id', $siswaId)->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat Pembayaran',
            'data' => $pembayaranSiswa,
        ]);
    }

    // Metode untuk riwayat tagihan
    public function riwayatTagihan(Request $request)
    {
        $siswaId = $request->user()->id; // Ambil ID siswa dari user yang sedang login

        // Ambil tagihan berdasarkan siswa_id dan status
        $tagihan = PembayaranSiswa::where('siswa_id', $siswaId)
            ->where('status', 0) // Misalnya, 0 = belum dibayar
            ->get();

        // Kembalikan data dalam format JSON
        return response()->json([
            'success' => true,
            'message' => 'Riwayat Tagihan',
            'data' => $tagihan,
        ]);
    }
}
