<?php

namespace App\Http\Controllers\Api;

use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Anggaran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AnggaranResource;
use App\Models\Sekolah;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

class AnggaranController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $query = Anggaran::oldest();

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
            'deskripsi' => 'nullable|string',
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

        if ($request->input('status') == 2) {
            $validator = Validator::make($request->all(), [
                'pengapprove' => 'required|string|max:225',
                'pengapprove_jabatan' => 'required|string|max:225',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
        }

        $anggaran = Anggaran::create($request->all());

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
            'deskripsi' => 'nullable|string',
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
        $weekdays = ["Senin", "Selasa", "Rabu", "Kamis", "Jum'at", "Sabtu"];
        $months = [
            "Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];
        $thisYear = now()->year;
        $thisMonth = now()->format('m'); // Always ensures 2-digit month
        $fiveYears = range($thisYear - 2, $thisYear + 2); // Range of 5 years

        // Donut Data
        $donut = [
            'week' => [
                'terealisasi' => Anggaran::whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->whereIn('status', [2, 3])->count(),
                'keseluruhan' => Anggaran::whereBetween('tanggal_pengajuan', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            ],
            'month' => [
                'terealisasi' => Anggaran::whereYear('updated_at', $thisYear)
                    ->whereMonth('updated_at', $thisMonth)
                    ->whereIn('status', [2, 3])->count(),
                'keseluruhan' => Anggaran::whereYear('tanggal_pengajuan', $thisYear)
                    ->whereMonth('tanggal_pengajuan', $thisMonth)->count(),
            ],
            'year' => [
                'terealisasi' => Anggaran::whereYear('updated_at', $thisYear)
                    ->whereIn('status', [2, 3])->count(),
                'keseluruhan' => Anggaran::whereYear('tanggal_pengajuan', $thisYear)->count(),
            ],
            'all' => [
                'terealisasi' => Anggaran::whereIn('status', [2, 3])->count(),
                'keseluruhan' => Anggaran::count(),
            ],
        ];

        // Daily Data
        $daily = [];
        foreach ($weekdays as $key => $day) {
            $currentDay = now()->startOfWeek()->addDays($key); // Adjust the day within the week
            $daily[$day] = [
                'realized' => Anggaran::whereDate('updated_at', $currentDay)
                    ->whereIn('status', [2, 3])->count(),
                'planned' => Anggaran::whereDate('tanggal_pengajuan', $currentDay)
                    ->whereIn('status', [2, 3])->count(),
            ];
        }

        // Monthly Data
        $monthly = [];
        foreach ($months as $key => $month) {
            $monthIndex = $key + 1;
            $monthly[$month] = [
                'realized' => Anggaran::whereYear('updated_at', $thisYear)
                    ->whereMonth('updated_at', $monthIndex)
                    ->whereIn('status', [2, 3])->count(),
                'planned' => Anggaran::whereYear('tanggal_pengajuan', $thisYear)
                    ->whereMonth('tanggal_pengajuan', $monthIndex)
                    ->whereIn('status', [2, 3])->count(),
            ];
        }

        // Yearly Data
        $yearly = [];
        foreach ($fiveYears as $year) {
            $yearly[$year] = [
                'realized' => Anggaran::whereYear('updated_at', $year)
                    ->whereIn('status', [2, 3])->count(),
                'planned' => Anggaran::whereYear('tanggal_pengajuan', $year)
                    ->whereIn('status', [2, 3])->count(),
            ];
        }

        // Compile all the data
        $chart = [
            'daily' => $daily,
            'monthly' => $monthly, // Fixed typo from "montly"
            'yearly' => $yearly,
        ];

        $data = [
            'donut' => $donut,
            'chart' => $chart,
        ];

        return response()->json($data);
    }

    public function printDeviasi(Request $request)
    {
        $tgl_awal = $request->query('tgl_awal'); // gunakan query parameter
        $tgl_akhir = $request->query('tgl_akhir');

        if ($tgl_awal && $tgl_akhir) {
            $anggaran = Anggaran::whereBetween('created_at', [$tgl_awal, $tgl_akhir])->get();
            $fileName = "Laporan Deviasi {$tgl_awal} - {$tgl_akhir}.pdf";
        } else {
            $anggaran = Anggaran::all();
            $fileName = "Laporan Deviasi Keseluruhan.pdf";
        }

        $data = [
            'deviasis' => $anggaran,
            'sekolah' => Sekolah::first()
        ];

        $pdf = Pdf::loadView('Print.Deviasi', $data);

        return $pdf->stream($fileName);
    }
}
