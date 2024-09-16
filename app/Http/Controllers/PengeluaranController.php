<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Models\PengeluaranKategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseFormatSame;

class PengeluaranController extends Controller
{
    public function index()
    {
        $pengeluaran = Pengeluaran::with('pengeluaran_kategori')->get();

        return response()->json([
            'success' => true,
            'message' => 'data pengeluaran berhasil diambil',
            'data' => $pengeluaran
        ]);
    }

    public function show(string $id)
    {
        $pengeluaran = Pengeluaran::with('pengeluaran_kategori')->find($id);

        if (!$pengeluaran) {
            return response()->json([
                'success' => false,
                'message' => 'data pengeluaran tidak ditemukan',
                'data' => $pengeluaran
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'data pengeluaran berhasil diambil',
            'data' => $pengeluaran
        ]);
    }

    public function getPengeluaranDisetujui()
    {
        $pengeluaran = Pengeluaran::with('pengeluaran_kategori')->where('disetujui_pada', '!=', null)->get();

        if ($pengeluaran->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'data pengeluaran tidak ditemukan',
                'data' => $pengeluaran
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'data pengeluaran berhasil diambil',
            'data' => $pengeluaran
        ]);
    }

    public function getPengeluaranBelumDisetujui()
    {
        $pengeluaran = Pengeluaran::with('pengeluaran_kategori')->where('disetujui_pada', '=', null)->get();

        if ($pengeluaran->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'data pengeluaran tidak ditemukan',
                'data' => $pengeluaran
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'data pengeluaran berhasil diambil',
            'data' => $pengeluaran
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pengeluaran_kategori_id' => 'required|exists:pengeluaran_kategori,id',
            'keperluan' => 'required',
            'nominal' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $role = auth()->user()->role;

        if ($role !== 'Bendahara') {
            $pengeluaran = Pengeluaran::create([
                'pengeluaran_kategori_id' => $request->pengeluaran_kategori_id,
                'keperluan' => $request->keperluan,
                'nominal' => $request->nominal,
                'diajukan_pada' => now(),
            ]);
        }

        $pengeluaran = Pengeluaran::create([
            'pengeluaran_kategori_id' => $request->pengeluaran_kategori_id,
            'keperluan' => $request->keperluan,
            'nominal' => $request->nominal,
            'diajukan_pada' => now(),
            'disetujui_pada' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'pengeluaran berhasil ditambahkan',
            'data' => $pengeluaran
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'pengeluaran_kategori_id' => 'required|exists:pengeluaran_kategori,id',
            'keperluan' => 'required',
            'nominal' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $pengeluaran = Pengeluaran::find($id);

        if (!$pengeluaran) {
            return response()->json([
                'success' => false,
                'message' => 'pengeluaran tidak ditemukan'
            ]);
        }

        $pengeluaran->update([
            'pengeluaran_kategori_id' => $request->pengeluaran_kategori_id,
            'keperluan' => $request->keperluan,
            'nominal' => $request->nominal,
            'diajukan_pada' => now(),
            'disetujui_pada' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'pengeluaran berhasil di ubah',
            'data' => $pengeluaran
        ]);
    }

    public function destroy(string $id)
    {

        $pengeluaran = Pengeluaran::find($id);

        if (!$pengeluaran) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'pengeluaran tidak ditemukan'
                ],
                404
            );
        }

        $pengeluaran->delete();

        return response()->json([
            'success' => true,
            'message' => 'pengeluaran berhasil dihapus',
            'data' => $pengeluaran
        ]);
    }

    public function acceptPengeluaran(string $id)
    {
        $pengeluaran = Pengeluaran::find($id);

        if (!$pengeluaran) {
            return response()->json([
                'success' => false,
                'message' => 'pengeluaran tidak ditemukan'
            ], 404);
        }

        $role = auth()->user()->role;
        if ($role !== 'Bendahara') {
            abort(403);
        }

        if ($pengeluaran->disetujui_pada) {
            return response()->json([
                'success' => false,
                'message' => 'pengeluaran sudah diterima',
            ], 409);
        }

        $pengeluaran->update([
            'disetujui_pada' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'pengeluaran berhasil diterima',
            'data' => $pengeluaran
        ]);
    }

    public function riwayatPengeluaran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'kategori_id' => 'nullable|exists:pengeluaran_kategori,id',
            'status' => 'nullable|in:disetujui,belum_disetujui',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $kategoriId = $request->input('kategori_id');
        $status = $request->input('status');

        $query = Pengeluaran::with('pengeluaran_kategori');

        if ($startDate) {
            $query->where('diajukan_pada', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('diajukan_pada', '<=', $endDate);
        }
        if ($kategoriId) {
            $query->where('pengeluaran_kategori_id', $kategoriId);
        }
        if ($status) {
            if ($status == 'disetujui') {
                $query->whereNotNull('disetujui_pada');
            } elseif ($status == 'belum_disetujui') {
                $query->whereNull('disetujui_pada');
            }
        }

        $pengeluaran = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Data riwayat pengeluaran berhasil diambil',
            'data' => $pengeluaran
        ]);
    }

    public function rekapitulasiPengeluaran($periode)
    {
        if (!in_array($periode, ['harian', 'mingguan', 'bulanan', 'tahunan'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid periode value',
            ], 422);
        }

        $query = Pengeluaran::with('pengeluaran_kategori');

        switch ($periode) {
            case 'harian':
                $query->whereDate('created_at', now()->toDateString());
                break;
            case 'mingguan':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'bulanan':
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
                break;
            case 'tahunan':
                $query->whereYear('created_at', now()->year);
                break;
        }

        $pengeluaran = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Data rekapitulasi pengeluaran berhasil diambil',
            'data' => $pengeluaran
        ]);
    }
}
