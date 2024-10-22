<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\Request;
use App\Exports\PembayaranExport;
use App\Models\PembayaranDuitku;
use App\Models\PembayaranPpdb;
use App\Models\Ppdb;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Models\PembayaranKategori;
use App\Models\Pendaftar;
use App\Models\PendaftarAkademik;
use App\Models\PendaftarDokumen;
use App\Models\User;
use App\Notifications\CredentialsEmailNotification;
use GrahamCampbell\ResultType\Success;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class PembayaranController extends Controller
{
    const PDF_STORAGE_PATH = 'storage/app/documents/';

    // Menampilkan semua data pembayaran
    public function index()
    {
        $pembayarans = Pembayaran::all();
        return response()->json($pembayarans);
    }
    public function create()
    {
        //
    }

    // Menyimpan pembayaran baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'pembayaran_kategori_id' => 'required|exists:pembayaran_kategori,id',
            'nominal' => 'required|numeric',
            'status' => 'required|string',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $pembayaran = Pembayaran::create($validatedData);

        return response()->json([
            'message' => 'Pembayaran berhasil ditambahkan',
            'data' => $pembayaran
        ], 201);
    }

    // Menampilkan detail pembayaran berdasarkan ID
    public function show($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        return response()->json($pembayaran);
    }

    // Menampilkan form untuk mengedit pembayaran (jika diperlukan)
    public function edit($id)
    {
        //
    }

    // Memperbarui data pembayaran
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'pembayaran_kategori_id' => 'required|exists:pembayaran_kategori,id',
            'nominal' => 'required|numeric',
            'status' => 'required|string',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $pembayaran = Pembayaran::find($id);
        $pembayaran->update($validatedData);

        return response()->json([
            'message' => 'Pembayaran berhasil diperbarui',
            'data' => $pembayaran
        ], 200);
    }

    // Menghapus pembayaran (soft delete)
    public function destroy($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $pembayaran->delete();

        return response()->json([
            'message' => 'Pembayaran berhasil dihapus'
        ], 200);
    }

    public function exportPembayaranPpdb(Request $request)
    {
        $year = $request->input('tahun_awal', date('Y')); // Default ke tahun sekarang jika tidak ada parameter

        // Pass the selected year to the export class
        return Excel::download(new PembayaranExport($year), 'dataPPDB.xlsx');
    }
    
    public function getPaymentMethod(Request $request)
    {
        // Validate input from request
        $request->validate([
            'merchantCode' => 'string',
            'apiKey' => 'string',
            'paymentAmount' => 'numeric',
            'paymentMethod' => 'nullable|string', 
        ]);

        $merchantCode = 'DS19869';
        $apiKey = '8093b2c02b8750e4e73845f307325566';
        $paymentAmount = $request->input('paymentAmount');
        $paymentMethod = $request->get('paymentMethod');
        $datetime = now()->format('Y-m-d H:i:s');

        $signature = hash('sha256', $merchantCode . $paymentAmount . $datetime . $apiKey);


        $params = [
            'merchantcode' => $merchantCode,
            'amount' => $paymentAmount,
            'datetime' => $datetime,
            'signature' => $signature,
            'paymentMethod' => $paymentMethod,
        ];

        $url = 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod';

        $client = new Client();

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Content-Length' => strlen(json_encode($params)),
                ],
                'body' => json_encode($params),
                'verify' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);

            if ($statusCode == 200) {
                return response()->json($responseBody, 200);
            } else {
                return response()->json(
                    [
                        'error' => 'Server Error',
                        'message' => $responseBody['Message'] ?? 'An error occurred',
                    ],
                    $statusCode,
                );
            }
        } catch (\Exception $e) {
            Log::error('Error retrieving payment methods from Duitku', ['message' => $e->getMessage()]);
            return response()->json(
                [
                    'error' => 'Request Error',
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function createTransaction(Request $request)
    {
        $merchantCode = env('DUITKU_MERCHANT_CODE');
        $apiKey = env('DUITKU_API_KEY');  
        $paymentAmount = $request->input('paymentAmount');
        $first_name = $request->input('nama_depan');
        $last_name = $request->input('nama_belakang');
        $paymentMethod = $request->input('paymentMethod');
        $additionalParam = 'PPDB';
        $merchantOrderId = $request->input('merchantOrderId');
        $callbackUrl = env('DUITKU_CALLBACK_URL');
        $returnUrl = 'http://localhost:5173/orang-tua/cek-pembayaran';
        $expiryPeriod = 60;
        $customerEmail = $request->input('email');
        $customerVaName = $first_name . ' ' . $last_name;
        $signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $apiKey);

        Log::info('Signature generated in createTransaction', ['signature' => $signature]);

        $params = [
            'merchantCode' => $merchantCode,
            'nama_depan' => $first_name,
            'nama_belakang' => $last_name,
            'paymentAmount' => $paymentAmount,
            'addtionalParam' => $additionalParam,
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $merchantOrderId,
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature,
            'expiryPeriod' => $expiryPeriod,
            'email' => $customerEmail,
            'customerVaName' => $customerVaName,
        ];

        $params_string = json_encode($params);
        $url = 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry'; // Sandbox

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($params_string)]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Execute cURL request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $responseBody = json_decode($response, true);
            $responseBody['signature'] = $signature;    
            $responseBody['merchantOrderId'] = $merchantOrderId;
            $userId = Auth::id();

            $pembayaranDuitku = PembayaranDuitku::where('merchant_order_id', $merchantOrderId)->first();

            $pembayaranDuitku->update([
                'reference' => $responseBody['reference'],
                'payment_method' => $paymentMethod,
                'transaction_response' => json_encode($responseBody),
                'callback_response' => null,
            ]);

            $ppdb = Ppdb::create([
                'user_id' => $userId,
                'status' => 1,
                'merchant_order_id' => $merchantOrderId,
            ]);

            $request->session()->put('ppdb_id', $ppdb->id);

            $pembayaran = Pembayaran::create([
                'siswa_id' => null,
                'pembayaran_kategori_id' => 1,
                'nominal' => $paymentAmount,
                'status' => 0,
                'kelas_id' => null,
                'ppdb_id' => $ppdb->id,
            ]);

            PembayaranPpdb::create([
                'ppdb_id' => $ppdb->id,
                'pembayaran_id' => $pembayaran->id,
                'nominal' => $paymentAmount,
                'merchant_order_id' => $merchantOrderId,
                'status' => 0,
            ]);

            return response()->json($responseBody);
        } else {
            return response()->json(
                [
                    'error' => 'Server Error',
                    'message' => json_decode($response)->Message ?? 'Unknown error',
                ],
                $httpCode,
            );
        }
    }

    public function handleCallback(Request $request)
    {
        try {
            $apiKey = '8093b2c02b8750e4e73845f307325566';
            $merchantCode = 'DS19869';
            $amount = $request->input('amount');
            $merchantOrderId = $request->input('merchantOrderId');
            $signature = $request->input('signature');
            $resultCode = $request->input('resultCode'); // Get resultCode from the request
    
            Log::info('Data received from Duitku', [
                'merchantCode' => $merchantCode,
                'amount' => $amount,
                'merchantOrderId' => $merchantOrderId,
                'signature' => $signature,
                'resultCode' => $resultCode, // Log resultCode
            ]);
    
            // Calculate signature
            $params = $merchantCode . $amount . $merchantOrderId . $apiKey;
            $calcSignature = md5($params);
    
            Log::info('Calculated Signature', ['calcSignature' => $calcSignature]);
    
            if ($signature == $calcSignature) {
                if ($resultCode == '00') {
                    Log::info("Callback valid and successful for Order ID: $merchantOrderId, PaymentAmount: $amount");
    
                    $pembayaran = PembayaranDuitku::where('merchant_order_id', $merchantOrderId)->first();
    
                    if ($pembayaran) {
                        try {
                            // Update status pembayaran menjadi 'success' dan simpan callback response
                            $pembayaran->update([
                                'status' => 'Success',
                                'callback_response' => json_encode($request->all()),
                            ]);
    
                            // Update status Ppdb menjadi 2
                            $ppdb = Ppdb::where('merchant_order_id', $merchantOrderId)->first();
                            if ($ppdb) {
                                $ppdb->update(['status' => 2]);
                                Log::info("Ppdb status updated to 2 for Order ID: $merchantOrderId");
    
                                // Update status PembayaranPpdb menjadi 1
                                $pembayaranPpdb = PembayaranPpdb::where('merchant_order_id', $merchantOrderId)->first();
                                if (!$pembayaranPpdb) {
                                    Log::error("PembayaranPpdb record not found for Order ID: $merchantOrderId");
                                    return response()->json(['error' => 'PembayaranPpdb record not found'], 404);
                                }
                                $pembayaranPpdb->update(['status' => 1]);
                                Log::info("PembayaranPpdb status updated to 1 for Order ID: $merchantOrderId");
    
                                // Decode user data and insert into Pendaftar
                                $dataUserResponse = json_decode($pembayaran->data_user_response, true);
    
                                if (!$dataUserResponse) {
                                    Log::error("Failed to decode data_user_response for Order ID: $merchantOrderId");
                                    return response()->json(['error' => 'Failed to decode user data'], 500);
                                }
                                // Insert Pendaftar
                                Pendaftar::create([
                                    'ppdb_id' => $ppdb->id,
                                    'nama_depan' => $dataUserResponse['nama_depan'],
                                    'nama_belakang' => $dataUserResponse['nama_belakang'],
                                    'jenis_kelamin' => $dataUserResponse['jenis_kelamin'],
                                    'nik' => $dataUserResponse['nik'],
                                    'email' => $dataUserResponse['email'],
                                    'nisn' => $dataUserResponse['nisn'],
                                    'tempat_lahir' => $dataUserResponse['tempat_lahir'],
                                    'tgl_lahir' => $dataUserResponse['tgl_lahir'],
                                    'alamat' => $dataUserResponse['alamat'],
                                    'village_id' => $dataUserResponse['village_id'],
                                    'nama_ayah' => $dataUserResponse['nama_ayah'],
                                    'nama_ibu' => $dataUserResponse['nama_ibu'],
                                    'tgl_lahir_ayah' => $dataUserResponse['tgl_lahir_ayah'],
                                    'tgl_lahir_ibu' => $dataUserResponse['tgl_lahir_ibu'],
                                ]);
    
                                PendaftarDokumen::create([
                                    'ppdb_id' => $ppdb->id,
                                    'akte_kelahiran' => $dataUserResponse['akte_kelahiran'],
                                    'kartu_keluarga' => $dataUserResponse['kartu_keluarga'],
                                    'ijazah' => $dataUserResponse['ijazah'],
                                    'raport' => $dataUserResponse['raport'],
                                ]);
    
                                // Insert PendaftarAkademik
                                PendaftarAkademik::create([
                                    'ppdb_id' => $ppdb->id,
                                    'sekolah_asal' => $dataUserResponse['sekolah_asal'],
                                    'tahun_lulus' => $dataUserResponse['tahun_lulus'],
                                    'jurusan_tujuan' => $dataUserResponse['jurusan_tujuan'],
                                ]);
    
                                $plainPassword = Str::random(12);
    
                                $user = User::create([
                                    'name' => $dataUserResponse['nama_depan'] . ' ' . $dataUserResponse['nama_belakang'],
                                    'email' => $dataUserResponse['email'],
                                    'password' => Hash::make($plainPassword),
                                    'role' => 'Siswa',
                                    'email_verified_at' => now(),
                                    'remember_token' => Str::random(10),
                                ]);
    
                                $user->notify(new CredentialsEmailNotification($plainPassword));
    
                                Log::info("Data user successfully inserted into Pendaftar for Order ID: $merchantOrderId");
                            }
                        } catch (\Exception $e) {
                            Log::error('Error updating payment, Ppdb, or PembayaranPpdb record: ' . $e->getMessage());
                            return response()->json(['error' => 'Update Error', 'message' => $e->getMessage()], 500);
                        }
                    } else {
                        Log::error("Payment record not found for Order ID: $merchantOrderId");
                        return response()->json(['error' => 'Payment record not found'], 404);
                    }
    
                    return response()->json(['message' => 'Callback processed successfully', 'merchantOrderId' => $merchantOrderId], 200);
                } else {
                    Log::info("Callback failed for Order ID: $merchantOrderId, resultCode: $resultCode");
                    return response()->json(['error' => 'Transaction failed', 'merchantOrderId' => $merchantOrderId, 'resultCode' => $resultCode], 400);
                }
            } else {
                Log::error("Bad signature for Order ID: $merchantOrderId");
                return response()->json(['error' => 'Bad signature'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Unexpected error in handleCallback', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Unexpected Error', 'message' => $e->getMessage()], 500);
        }
    }
}
