<?php

namespace App\Http\Controllers\Api;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Anggaran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AnggaranResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

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
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Additional validation if status is 'Diapprove'
        if ($request->input('status') == 2) {
            $validator->sometimes('pengapprove', 'required|string|max:225', function ($input) {
                return !empty($input->pengapprove);
            });
            $validator->sometimes('pengapprove_jabatan', 'required|string|max:225', function ($input) {
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
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->input('status') == 2) {
            $validator->sometimes('pengapprove', 'required|string|max:225', function ($input) {
                return !empty($input->pengapprove);
            });
            $validator->sometimes('pengapprove_jabatan', 'required|string|max:225', function ($input) {
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

    public function getAnggaranData(Request $request)
    {
        $period = $request->query('period', 'monthly');
        
        $query = Anggaran::query();
        
        switch ($period) {
            case 'weekly':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'monthly':
                $query->whereYear('created_at', now()->year)
                      ->whereMonth('created_at', now()->month);
                break;
            case 'yearly':
                $query->whereYear('created_at', now()->year);
                break;
            case 'all':
                // No additional filters for 'all'
                break;
            default:
                // Handle invalid period
                return response()->json(['error' => 'Invalid period'], 400);
        }
    
        $diajukan = $query->where('status', 1)->sum('nominal');
        $diapprove = $query->where('status', 2)->sum('nominal');
        $total = $diajukan + $diapprove;
        $persentaseRealisasi = ($total > 0) ? ($diapprove / $total) * 100 : 0;
    
        $data = [
            'series' => [
                $diajukan,
                $diapprove
            ],
            'labels' => ['Diajukan', 'Diapprove'],
            'percentage' => $persentaseRealisasi
        ];
    
        return response()->json($data);
    }

    

    public function getAnggaranColum(Request $request)
{
    $period = $request->query('period', 'monthly');

    $query = Anggaran::query();
    
    switch ($period) {
        case 'weekly':
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            break;
        case 'monthly':
            $query->whereYear('created_at', now()->year)
                  ->whereMonth('created_at', now()->month);
            break;
        case 'yearly':
            $query->whereYear('created_at', now()->year);
            break;
        case 'all':
            // No additional filters for 'all'
            break;
        default:
            // Handle invalid period
            return response()->json(['error' => 'Invalid period'], 400);
    }

    $keseluruhan = $query->whereNot('status', 3)->count();
    $terealisasi = $query->where('status', 3)->count();
    
    $jumlahTerapproveTahunIni = $query->whereYear('created_at', now()->year)
                                      ->where('status', 3)
                                      ->count();
    $jumlahTerapproveBulanIni = $query->whereYear('created_at', now()->year)
                                      ->whereMonth('created_at', now()->month)
                                      ->where('status', 3)
                                      ->count();

    $jumlahtahunini = $query->selectRaw('MONTH(created_at) as month, COUNT(*) as count, SUM(nominal) as sum')
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

    $jumlahbulanini = $query->selectRaw('WEEK(created_at, 1) as week, COUNT(*) as count, SUM(nominal) as sum')
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

    $totalRencanaAnggaranTahunIni = $query->whereYear('created_at', now()->year)
                                         ->sum('nominal');
    $totalRencanaAnggaranBulanIni = $query->whereYear('created_at', now()->year)
                                          ->whereMonth('created_at', now()->month)
                                          ->sum('nominal');

    return response()->json([
        'total_keseluruhan' => $keseluruhan,
        'total_terealisasi' => $terealisasi,
        'jumlah_terapprove_tahun_ini' => $jumlahTerapproveTahunIni,
        'jumlah_terapprove_bulan_ini' => $jumlahTerapproveBulanIni,
        'jumlah_tahun_ini' => $jumlahtahunini,
        'jumlah_bulan_ini' => $jumlahbulanini,
        'total_rencana_anggaran_tahun_ini' => $totalRencanaAnggaranTahunIni,
        'total_rencana_anggaran_bulan_ini' => $totalRencanaAnggaranBulanIni,
    ]);
}

public function printDeviasi(Request $request)
    {
        $tgl_awal = request('tgl_awal');
            $tgl_akhir = request('tgl_akhir');

            if ($tgl_awal && $tgl_akhir) {
                $anggaran = Anggaran::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
                $fileName = "Aset {$tgl_awal} - {$tgl_akhir}.pdf";
            } else {
                $anggaran = Anggaran::all();
                $fileName = "Data Keseluruhan Deviasi.pdf";
            }

            $data = ['deviasis' => $anggaran];
            $pdf = Pdf::loadView('Print.Deviasi', $data);

            return $pdf->stream($fileName);
    }
}
