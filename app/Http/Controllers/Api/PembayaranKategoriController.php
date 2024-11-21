<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PembayaranKategoriResource;
use App\Http\Services\WatZapService;
use App\Models\Pembayaran;
use App\Models\PembayaranKategori;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PembayaranKategoriController extends Controller
{

    protected $watZapService;

    public function __construct(WatZapService $watZapService)
    {
        $this->watZapService = $watZapService;
    }

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

public function sendPaymentReminder()  
{
    // Ambil data pembayaran yang statusnya aktif dan jenisnya bulanan atau tahunan
    $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
        $query->whereIn('jenis_pembayaran', [1, 2]) // Pembayaran bulanan dan tahunan
              ->where('status', 1); // Hanya yang aktif
    })
    ->whereNull('deleted_at')
    ->get();

    // Ambil tanggal sekarang
    $currentDate = now();

    foreach ($pembayaranList as $pembayaran) {
        // Ambil data siswa
        $siswa = $pembayaran->siswa;
        $phoneNumber = $siswa->telepon;

        // Tentukan tanggal jatuh tempo
        if ($pembayaran->pembayaran_kategori->jenis_pembayaran == 1) {
            // Jenis pembayaran bulanan: Gunakan tanggal `created_at` dan hitung 3 hari setelahnya
            $createdDate = \Carbon\Carbon::parse($pembayaran->created_at);
            $dueDate = $createdDate->addDays(3);

            // Detail pembayaran untuk bulanan: tagihan ke dan nominal
            $paymentDetails = [
                'tagihanKe' => $pembayaran->pembayaran_ke,
                'nominal' => $pembayaran->nominal
            ];

        } else {
            // Jenis pembayaran tahunan: Gunakan tanggal pembayaran yang ada (tanggal bulan tertentu)
            $dueDate = \Carbon\Carbon::createFromFormat('d-m', $pembayaran->pembayaran_kategori->tanggal_pembayaran);
            // Tentukan tahun jatuh tempo (gunakan tahun sekarang jika belum lewat, jika sudah gunakan tahun depan)
            if ($dueDate->lt($currentDate)) {
                $dueDate->addYear();
            }

            // Detail pembayaran untuk tahunan: hanya nominal
            $paymentDetails = [
                'nominal' => $pembayaran->nominal
            ];
        }

        // Hitung berapa hari lagi jatuh tempo
        $daysUntilDue = $currentDate->diffInDays($dueDate);

        // Kirim pengingat jika jatuh tempo dalam 3 hari
        if ($daysUntilDue == 3) {
            // Mengirim pesan pengingat via WhatsApp dengan detail pembayaran
            $response = $this->watZapService->sendReminder(
                $phoneNumber, 
                $siswa->nama_depan, 
                $pembayaran->pembayaran_kategori->nama, 
                $dueDate->toFormattedDateString(),
                $paymentDetails
            );

            // Log pengiriman
            if (isset($response['error'])) {
                Log::error('Gagal mengirim WhatsApp: ' . $response['error']);
            } else {
                Log::info('Pesan WhatsApp berhasil terkirim ke: ' . $phoneNumber);
            }
        }
    }

    return response()->json(['success' => true, 'message' => 'Pengingat pembayaran telah dikirim.']);
}


}