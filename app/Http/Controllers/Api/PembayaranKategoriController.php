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
    $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
        $query->where('jenis_pembayaran', 1)->where('status', 1); // Pembayaran bulanan atau tahunan
    })
    ->whereNull('deleted_at')
    ->get();

    // Mengambil tanggal sekarang
    $currentDate = now();

    foreach ($pembayaranList as $pembayaran) {
        // Menggunakan `created_at` untuk menghitung jatuh tempo
        $createdDate = \Carbon\Carbon::parse($pembayaran->created_at);
        $dueDate = $createdDate->addDays(3); // Menghitung 3 hari setelah `created_at`

        $daysUntilDue = $currentDate->diffInDays($dueDate);

        if ($daysUntilDue == 3) {
            $siswa = $pembayaran->siswa;
            $phoneNumber = $siswa->telepon;

            $message = "Halo, " . $siswa->nama_depan . ". Pembayaran untuk " . $pembayaran->pembayaran_kategori->nama . " Anda akan jatuh tempo dalam 3 hari, yaitu pada " . $dueDate->toFormattedDateString() . ". Jangan lupa untuk melakukan pembayaran tepat waktu.";

            // Mengirim pesan pengingat via WhatsApp
            $response = $this->watZapService->sendReminder($phoneNumber, $siswa->nama_depan, $pembayaran->pembayaran_kategori->nama, $dueDate->toFormattedDateString());

            // Menambahkan log untuk memastikan pesan berhasil terkirim
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