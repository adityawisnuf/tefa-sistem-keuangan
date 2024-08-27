<?php

namespace App\Http\Controllers\Api;

use App\Models\Anggaran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AnggaranResource;
use Illuminate\Support\Facades\Validator;

class AnggaranController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $query = Anggaran::oldest(); // Mengurutkan berdasarkan tanggal terlama

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_anggaran', 'like', "%$search%")
                    ->orWhere('nominal', 'like', "%$search%")
                    ->orWhere('deskripsi', 'like', "%$search%")
                    ->orWhere('tanggal_pengajuan', 'like', "%$search%")
                    ->orWhere('target_terealisasikan', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%")
                    ->orWhere('pengapprove', 'like', "%$search%")
                    ->orWhere('pengapprove_jabatan', 'like', "%$search%")
                    ->orWhere('catatan', 'like', "%$search%");
            });
        }

        $anggaran = $request->input('page') === 'all' ? $query->get() : $query->paginate(5); // Menggunakan pagination

        return new AnggaranResource(true, 'List Anggaran', $anggaran);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_anggaran' => 'string|max:225',
            'nominal' => 'numeric',
            'deskripsi' => 'string',
            'tanggal_pengajuan' => 'date',
            'target_terealisasikan' => 'date|nullable',
            'status' => 'integer|in:1,2,3,4',
            'pengapprove' => 'nullable|string|max:225',
            'pengapprove_jabatan' => 'nullable|string|max:225',
            'nominal_diapprove' => 'numeric|nullable',
            'catatan' => 'nullable',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Additional validation if status is 'Diapprove'
        if ($request->input('status') == 2) {
            $validator->sometimes('pengapprove', 'nullable|string|max:225', function ($input) {
                return !empty($input->pengapprove);
            });
            $validator->sometimes('pengapprove_jabatan', 'nullable|string|max:225', function ($input) {
                return !empty($input->pengapprove_jabatan);
            });
        }

        $anggaran = Anggaran::create($validator->validated());

        return new AnggaranResource(true, 'Anggaran Baru Berhasil Ditambahkan!', $anggaran);
    }

    public function show($id)
    {
        $anggaran = Anggaran::find($id);
        if (!$anggaran) {
            return response()->json(['success' => false, 'message' => 'Anggaran tidak ditemukan'], 404);
        }
        return new AnggaranResource(true, 'Detail Data Anggaran!', $anggaran);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_anggaran' => 'string|max:225',
            'nominal' => 'numeric',
            'deskripsi' => 'string',
            'tanggal_pengajuan' => 'date',
            'target_terealisasikan' => 'date',
            'status' => 'integer|in:1,2,3,4',
            'pengapprove' => 'nullable|string|max:225',
            'pengapprove_jabatan' => 'nullable|string|max:225',
            'nominal_diapprove' => 'numeric',
            'catatan' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->input('status') == 2) {
            $validator->sometimes('pengapprove', 'nullable|string|max:225', function ($input) {
                return !empty($input->pengapprove);
            });
            $validator->sometimes('pengapprove_jabatan', 'nullable|string|max:225', function ($input) {
                return !empty($input->pengapprove_jabatan);
            });
        }

        $anggaran = Anggaran::find($id);
        if (!$anggaran) {
            return response()->json(['success' => false, 'message' => 'Anggaran tidak ditemukan'], 404);
        }

        $anggaran->update($validator->validated());

        return new AnggaranResource(true, 'Anggaran Berhasil Diubah!', $anggaran);
    }

    public function destroy($id)
    {
        $anggaran = Anggaran::find($id);
        if (!$anggaran) {
            return response()->json(['success' => false, 'message' => 'Anggaran tidak ditemukan'], 404);
        }
        $anggaran->delete();
        return new AnggaranResource(true, 'Data Anggaran Berhasil Dihapus!', null);
    }

    public function getAnggaranData()
    {
        // Ambil data anggaran dengan status 'Diajukan' dan 'Diapprove'
        $diajukan = Anggaran::where('status', 1)->sum('nominal');
        $diapprove = Anggaran::where('status', 2)->sum('nominal');

        // Hitung total nominal untuk normalisasi
        $total = $diajukan + $diapprove;

        // Menyusun data untuk grafik
        $data = [
            'series' => [
                $diajukan / $total * 100, // Persentase dari 'Diajukan'
                $diapprove / $total * 100 // Persentase dari 'Diapprove'
            ],
            'labels' => ['Diajukan', 'Diapprove'] // Label untuk grafik
        ];

        return response()->json($data);
    }

    public function getAnggaranCount()
    {
        $keseluruhan = Anggaran::whereNot('status', 3)->count();
        $terealisasi = Anggaran::where('status', 3)->count();

        $jumlahTerapproveTahunIni = Anggaran::whereYear('created_at', now()->year)->where('status', 3)->count();
        $jumlahTerapproveBulanIni = Anggaran::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('status', 3)
            ->count();

        // Sum and count for each month of the current year
        $jumlahtahunini = Anggaran::selectRaw('MONTH(created_at) as month, COUNT(*) as count, SUM(nominal) as sum')
            ->whereYear('created_at', now()->year)
            ->where('status', 3)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    strtolower(now()->startOfYear()->addMonths($item->month - 1)->format('M')) => [
                        'count' => $item->count,
                        'sum of nominal' => $item->sum
                    ]
                ];
            });

        // Sum and count for each week of the current month
        $jumlahbulanini = Anggaran::selectRaw('WEEK(created_at, 1) as week, COUNT(*) as count, SUM(nominal) as sum')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('status', 3)
            ->groupBy('week')
            ->orderBy('week')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    'minggu' . $item->week => [
                        'count' => $item->count,
                        'sum of nominal' => $item->sum
                    ]
                ];
            });

        return new AnggaranResource(true, 'Jumlah anggaran berhasil didapatkan!', [
            'keseluruhan' => $keseluruhan,
            'terealisasi' => $terealisasi,
            'jumlahTerapproveTahunIni' => $jumlahTerapproveTahunIni,
            'jumlahTerapproveBulanIni' => $jumlahTerapproveBulanIni,
            'jumlahtahunini' => $jumlahtahunini,
            'jumlahbulanini' => $jumlahbulanini,
        ]);
    }
}
