<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PembayaranKategoriResource;
use App\Models\PembayaranKategori;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PembayaranKategoriController extends Controller
{
     /**
     * index
     *
     * @return void
     */
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $query = PembayaranKategori::oldest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                    ->orWhere('jenis_pembayaran', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%");
            });
        }

        $kategori = $request->input('page') === 'all' ? $query->get() : $query->paginate(5);
        return new PembayaranKategoriResource(true, 'List Pembayaran Kategori', $kategori);
    }

     /**{{  }}
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'jenis_pembayaran' => 'required|in:1,2',
            'tanggal_pembayaran' => 'required|string|max:255|regex:/^\d{2}(-\d{2})?$/',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kategori = PembayaranKategori::create($validator->validated());

        return new PembayaranKategoriResource(true, ' Pembayaran Kategori Baru Berhasil Ditambahkan', $kategori);
    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'jenis_pembayaran' => 'required|in:1,2',
            'tanggal_pembayaran' => 'required|string|max:255|regex:/^\d{2}(-\d{2})?$/',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kategori = PembayaranKategori::findOrFail($id);
        $kategori->update($validator->validated());

        return new PembayaranKategoriResource(true, 'Kategori Berhasil Diubah', $kategori);
    }


    public function destroy($id)
    {
        $kategori = PembayaranKategori::findOrFail($id);
        $kategori->delete();
        return new PembayaranKategoriResource(true, 'Data Kategori Berhasil Dihapus', $kategori);
    }

    public function notifications(Request $request) 
    {
        // Ambil semua pembayaran yang aktif
        $query = PembayaranKategori::where('status', 1);
    
        // Tambahkan filter berdasarkan `jenis_pembayaran` jika ada parameter
        if ($request->has('jenis_pembayaran')) {
            $query->where('jenis_pembayaran', $request->input('jenis_pembayaran'));
        }
    
        // Ambil kategori pembayaran
        $kategoriPembayaran = $query->get();
    
        $notifications = [];
        $currentDate = Carbon::today();
    
        foreach ($kategoriPembayaran as $kategori) {
            try {
                // Tentukan format tanggal berdasarkan jenis pembayaran
                if ($kategori->jenis_pembayaran == 1) { // Bulanan
                    $paymentDate = Carbon::createFromFormat('d', $kategori->tanggal_pembayaran)
                        ->setYear($currentDate->year)
                        ->setMonth($currentDate->month);
                } elseif ($kategori->jenis_pembayaran == 2) { // Tahunan
                    $paymentDate = Carbon::createFromFormat('d-m', $kategori->tanggal_pembayaran)
                        ->setYear($currentDate->year);
                } else {
                    continue; // Jika jenis tidak valid, lewati iterasi
                }
    
                // Tentukan jarak hari sampai tanggal jatuh tempo
                $daysUntilDue = $paymentDate->diffInDays($currentDate, false);
    
                // Notifikasi hanya untuk H-3 dan Hari H
                if ($daysUntilDue == 3 || $daysUntilDue == 0 || $daysUntilDue < 0) {
                    $notifications[] = [
                        'title' => $kategori->nama,
                        'time' => $paymentDate->translatedFormat('d F Y')
                    ];
                }
            } catch (\Exception $e) {
                // Log error jika ada masalah dalam parsing tanggal
                Log::error('Error parsing date for payment category ' . $kategori->id . ': ' . $e->getMessage());
            }
        }
    
        return response()->json($notifications);
    }
    
    
    
    
    public function peringatanJatuhTempo(Request $request)
{
    // Ambil semua pembayaran yang aktif
    $query = PembayaranKategori::where('status', 1);

    // Tambahkan filter berdasarkan `jenis_pembayaran` jika ada parameter
    if ($request->has('jenis_pembayaran')) {
        $query->where('jenis_pembayaran', $request->input('jenis_pembayaran'));
    }

    // Ambil kategori pembayaran
    $kategoriPembayaran = $query->get();
    $notifications = [];
    $currentDate = Carbon::today();

    foreach ($kategoriPembayaran as $kategori) {
        try {
            // Tentukan format tanggal berdasarkan jenis pembayaran
            if ($kategori->jenis_pembayaran == 1) { // Bulanan
                $paymentDate = Carbon::createFromFormat('d', $kategori->tanggal_pembayaran)
                    ->setYear($currentDate->year)
                    ->setMonth($currentDate->month);
            } elseif ($kategori->jenis_pembayaran == 2) { // Tahunan
                $paymentDate = Carbon::createFromFormat('d-m', $kategori->tanggal_pembayaran)
                    ->setYear($currentDate->year);
            } else {
                continue; // Jika jenis tidak valid, lewati iterasi
            }

            // Tentukan jarak hari sampai tanggal jatuh tempo
            $daysUntilDue = $paymentDate->diffInDays($currentDate, false); // `false` agar nilai negatif jika sudah jatuh tempo

            // Notifikasi hanya untuk H-3 dan Hari H
            if ($daysUntilDue == 3 || $daysUntilDue == 0 || $daysUntilDue < 0) {
                $notifications[] = [
                    'title' => $kategori->nama,
                    'due_time' => $paymentDate->translatedFormat('d F Y') // Menggunakan `due_time` di sini
                ];
            }
        } catch (\Exception $e) {
            // Log error jika ada masalah dalam parsing tanggal
            Log::error('Error parsing date for payment category ' . $kategori->id . ': ' . $e->getMessage());
        }
    }

    // Kembalikan notifikasi dalam format JSON
    return response()->json($notifications);
}
}