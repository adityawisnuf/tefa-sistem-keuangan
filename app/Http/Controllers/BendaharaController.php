<?php

namespace App\Http\Controllers;

use App\Models\KantinTransaksi;
use App\Models\LaundryTransaksiSatuan;
use App\Models\LaundryTransaksiKiloan;
use DateTime;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BendaharaController extends Controller
{
    private $startOfWeek;
    private $endOfWeek;

    public function __construct()
    {
        $this->startOfWeek = now()->startOfWeek();
        $this->endOfWeek = now()->endOfWeek();
    }

    public function index()
    {
        return response()->json([
            'data' => [
                'kantin_transaksi' => $this->getKantinTransaksi(),
                'laundry_transaksi_satuan' => $this->getLaundryTransaksiSatuan(),
                'laundry_transaksi_kiloan' => $this->getLaundryTransaksiKiloan(),
            ]
        ], Response::HTTP_OK);
    }

    public function getKantinTransaksi()
    {
        // Get the current date
        $perPage = request()->input('per_page', 10);
        return KantinTransaksi::whereBetween('tanggal_selesai', [$this->startOfWeek, $this->endOfWeek])->paginate($perPage);
    }

    public function getLaundryTransaksiSatuan()
    {
        $perPage = request()->input('per_page', 10);
        return LaundryTransaksiSatuan::whereBetween('tanggal_selesai', [$this->startOfWeek, $this->endOfWeek])->paginate($perPage);
    }

    public function getLaundryTransaksiKiloan()
    {
        $perPage = request()->input('per_page', 10);
        return LaundryTransaksiKiloan::whereBetween('tanggal_selesai', [$this->startOfWeek, $this->endOfWeek])->paginate($perPage);

    }
}
