<?php

namespace App\Http\Controllers;

use App\Http\Resources\PembayaranSiswaResource;
use App\Models\Pembayaran;
use App\Models\PembayaranDuitku;
use App\Models\PembayaranSiswa;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PembayaranManualController extends Controller
{
    public function getStudents(Request $request)
    {
        return response()->json(['success' => true, 'data' => $siswa = Siswa::whereAny(['user_id', 'nama_depan', 'nama_belakang', 'alamat', 'village_id', 'tempat_lahir', 'telepon', 'kelas_id', 'orangtua_id'], $request->input('query'))->get(), 'message' => "Berhasil mencari {$siswa->count()} data siswa"]);
    }
    public function getStudentPaymentList(Request $request)
    {
        $request->validate(['siswa_id' => 'numeric|required', 'type' => 'in:monthly,yearly|required']);
        switch ($request->type) {
            case 'monthly':
                return $this->getCurrentMonth($request);
            case 'yearly':
                return $this->getCurrentYear($request);
            default:
                return response()->json([
                    'error' => true,
                    'message' => 'Kolom tipe tidak boleh diluar dari monthly, yearly ataupun kosong'
                ]);
        }
    }
    private function getCurrentMonth(Request $request)
    {
        $siswa = Siswa::findOrFail($request->siswa_id);
        $months = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];

        $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
            $query->where('jenis_pembayaran', 1)
                ->where('status', 1);
        })
            ->where('siswa_id', $siswa->id)->orWhere('kelas_id', $siswa->kelas->id)
            ->with(['pembayaran_siswa' => function ($query) use ($siswa) {
                $query->where('siswa_id', $siswa->id)
                    ->with('pembayaran_siswa_cicilan');
            }, 'pembayaran_kategori'])
            ->get();


        if ($pembayaranList->isEmpty()) {
            $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
                $query->where('jenis_pembayaran', 1)
                    ->where('status', 1);
            })
                ->where('kelas_id', $siswa->kelas_id)
                ->with(['pembayaran_siswa' => function ($query) use ($siswa) {
                    $query->where('siswa_id', $siswa->id)
                        ->with('pembayaran_siswa_cicilan');
                }, 'pembayaran_kategori'])
                ->get();
        }

        $responseList = [];

        foreach ($pembayaranList as $pembayaran) {
            $pembayaran_siswa = $pembayaran->pembayaran_siswa->first();

            $totalCicilan = 0;
            $duitkuTransactions = [];

            if ($pembayaran_siswa && $pembayaran_siswa->pembayaran_siswa_cicilan->count() > 0) {
                $totalCicilan = $pembayaran_siswa->pembayaran_siswa_cicilan->sum('nominal_cicilan');
                foreach ($pembayaran_siswa->pembayaran_siswa_cicilan as $cicilan) {
                    $duitkuTransactions[] = $cicilan->pembayaran_duitku();
                }
            } elseif ($pembayaran_siswa) {
                $duitkuTransactions = PembayaranDuitku::where('merchant_order_id', $pembayaran_siswa->merchant_order_id)->get();
            }

            $responseList[] = [
                'id' => $pembayaran->id,
                'nama' => $pembayaran->pembayaran_kategori->nama,
                'tanggal_bayar' => $pembayaran->pembayaran_kategori->tanggal_pembayaran,
                'nominal' => $pembayaran->nominal,
                'transaction_requested' => $pembayaran_siswa ? true : false,
                'paid' => $pembayaran_siswa ? $pembayaran_siswa->status : false,
                'merchant_order_id' => $pembayaran_siswa ? $pembayaran_siswa->merchant_order_id : null,
                'pembayaran_ke' => $pembayaran->pembayaran_ke,
                'bulan' => $months[$pembayaran->pembayaran_ke - 1],
                'total_cicilan' => $totalCicilan,
                'duitku' => $duitkuTransactions,
            ];
        }


        $filteredPembayaranList = collect($responseList)->filter(function ($pembayaran) {
            return $pembayaran['paid'] === false || $pembayaran['paid'] === 0;
        });

        return response()->json([
            'success' => true,
            'nama' => $siswa->nama_depan . ($siswa->nama_belakang ? ' ' . $siswa->nama_belakang : ''),
            'jurusan' => $siswa->kelas->jurusan,
            'kelas' => $siswa->kelas->kelas,
            'orang_tua' => $siswa->orangtua->nama ?? '',
            'message' => 'List Pembayaran Siswa',
            'data' => $filteredPembayaranList,
        ]);
    }
    private function getCurrentYear(Request $request)
    {
        $siswa = Siswa::findOrFail($request->siswa_id);
        $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
            $query->where('jenis_pembayaran', 2)
                ->where('status', 1)
                ->whereYear('created_at', now()->year);
        })
            ->where(function ($query) use ($siswa) {

                $query->where('siswa_id', $siswa->id)
                    ->orWhere('kelas_id', $siswa->kelas->id);
            })
            ->with([
                'pembayaran_siswa' => function ($query) use ($siswa) {
                    $query->where('siswa_id', $siswa->id)
                        ->with('pembayaran_siswa_cicilan');
                },
                'pembayaran_kategori',
            ])
            ->get();


        if ($pembayaranList->isEmpty()) {
            $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
                $query->where('jenis_pembayaran', 2)
                    ->where('status', 1);
            })
                ->where('kelas_id', $siswa->kelas_id)
                ->with(['pembayaran_siswa' => function ($query) use ($siswa) {
                    $query->where('siswa_id', $siswa->id)
                        ->with('pembayaran_siswa_cicilan');
                }, 'pembayaran_kategori'])
                ->get();
        }

        $responseList = [];

        foreach ($pembayaranList as $pembayaran) {
            $pembayaran_siswa = $pembayaran->pembayaran_siswa->first();

            $totalCicilan = 0;
            $duitkuTransactions = [];

            if ($pembayaran_siswa && $pembayaran_siswa->pembayaran_siswa_cicilan->count() > 0) {
                $totalCicilan = $pembayaran_siswa->pembayaran_siswa_cicilan->sum('nominal_cicilan');
                foreach ($pembayaran_siswa->pembayaran_siswa_cicilan as $cicilan) {
                    $duitkuTransactions[] = $cicilan->pembayaran_duitku();
                }
            } elseif ($pembayaran_siswa) {
                $duitkuTransactions = PembayaranDuitku::where('merchant_order_id', $pembayaran_siswa->merchant_order_id)->get();
            }

            $responseList[] = [
                'id' => $pembayaran->id,
                'nama' => $pembayaran->pembayaran_kategori->nama,
                'tanggal_bayar' => $pembayaran->pembayaran_kategori->tanggal_pembayaran,
                'nominal' => $pembayaran->nominal,
                'transaction_requested' => $pembayaran_siswa ? true : false,
                'paid' => $pembayaran_siswa ? $pembayaran_siswa->status : false,
                'merchant_order_id' => $pembayaran_siswa ? $pembayaran_siswa->merchant_order_id : null,
                'total_cicilan' => $totalCicilan,
                'duitku' => $duitkuTransactions,
            ];
        }


        $filteredPembayaranList = collect($responseList)->filter(function ($pembayaran) {
            return $pembayaran['paid'] === false || $pembayaran['paid'] === 0;
        });

        return response()->json([
            'success' => true,
            'nama' => $siswa->nama_depan . ($siswa->nama_belakang ? ' ' . $siswa->nama_belakang : ''),
            'jurusan' => $siswa->kelas->jurusan,
            'kelas' => $siswa->kelas->kelas,
            'orang_tua' => $siswa->orangtua->nama ?? '',
            'message' => 'List Pembayaran Siswa',
            'data' => $filteredPembayaranList,
        ]);
    }
    public function payManually(Request $request)
    {
        $validate = $request->validate([
            'payment_method' => ['string', 'required'],
            'bukti_transaksi' => ['required', 'array'],
            'bukti_transaksi.nama' => ['required', 'string'],
            'bukti_transaksi.tanggal' => ['required', 'string'],
            'bukti_transaksi.keterangan' => ['nullable', 'string'],
            'bukti_transaksi.gambar' => ['required', 'string'],
            'siswa_id' => ['integer', 'required'],
            'pembayaran_id' => ['integer', 'exists:pembayaran,id'],
        ]);


        if (PembayaranSiswa::where('siswa_id', $validate['siswa_id'])->where('pembayaran_id', $validate['pembayaran_id'])->where('status', 1)->count() > 0) {
            return response()->json(['error' => true, 'message' => 'Pembayaran sudah dilakukan'], 409);
        }
        DB::beginTransaction();

        try {
            $siswa = Siswa::findOrFail($validate['siswa_id']);
            $pembayaran = Pembayaran::with('pembayaran_kategori')->findOrFail($validate['pembayaran_id']);
            $merchant_order_id = 'ORDM-0-' . time();

            $data = [
                'merchantOrderId' => $merchant_order_id,
                'item_details' => [
                    [
                        'name' => $pembayaran->pembayaran_kategori->nama,
                        'price' => $pembayaran->nominal,
                        'quantity' => 1,
                    ],
                ],
                'payment_amount' => $pembayaran->nominal,
                'user' => [
                    'name' => $siswa->nama_depan . ' ' . $siswa->nama_belakang,
                    'email' => $siswa->user->email,
                    'phone' => $siswa->telepon,
                ],
                'payment_method' => $validate['payment_method'],
                'title' => 'Pembayaran Siswa ' . $siswa->nama_depan,
            ];


            PembayaranDuitku::create([
                'merchant_order_id' => $merchant_order_id,
                'reference' => "MANUAL",
                'payment_method' => $validate['payment_method'],
                'transaction_response' => json_encode($validate['bukti_transaksi']),
                'status' => '00',
            ]);
            PembayaranSiswa::create([
                'siswa_id' => $siswa->id,
                'pembayaran_id' => $pembayaran->id,
                'nominal' => $pembayaran->nominal,
                'merchant_order_id' => $merchant_order_id,
                'status' => 1,
            ]);

            DB::commit();

            return new PembayaranSiswaResource(true, 'Permintaan bayar berhasil dilakukan!', [
                ...$data,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json(['error' => 'Gagal menyimpan transaksi: ' . $e->getMessage()], 500);
        }
    }
}
