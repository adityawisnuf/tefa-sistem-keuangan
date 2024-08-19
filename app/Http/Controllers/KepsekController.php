<?php

namespace App\Http\Controllers;

use App\Models\KantinPengajuan;
use App\Models\KantinTransaksi;
use App\Models\LaundryPengajuan;
use App\Models\LaundryTransaksiKiloan;
use App\Models\LaundryTransaksiSatuan;
use Illuminate\Http\Request;

class KepsekController extends Controller
{
    private $startOfWeek;
    private $endOfWeek;

    public function __construct()
    {
        $this->startOfWeek = now()->startOfWeek();
        $this->endOfWeek = now()->endOfWeek();
    }
    public function getKantinTransaksi()
    {
        $perPage = request()->input('per_page', 10);
        return KantinTransaksi::whereIn('status', ['dibatalkan', 'selesai'])
            ->whereBetween('tanggal_selesai', [$this->startOfWeek, $this->endOfWeek])
            ->paginate($perPage);
    }

    public function getLaundryTransaksiSatuan()
    {
        $perPage = request()->input('per_page', 10);
        return LaundryTransaksiSatuan::whereIn('status', ['dibatalkan', 'selesai'])
            ->whereBetween('tanggal_selesai', [$this->startOfWeek, $this->endOfWeek])
            ->paginate($perPage);
    }

    public function getLaundryTransaksiKiloan()
    {
        $perPage = request()->input('per_page', 10);
        return LaundryTransaksiKiloan::whereIn('status', ['dibatalkan', 'selesai'])
            ->whereBetween('tanggal_selesai', [$this->startOfWeek, $this->endOfWeek])
            ->paginate($perPage);
    }

    public function getKantinPengajuan()
    {
        $perPage = request()->input('per_page', 10);
        return KantinPengajuan::whereIn('status', ['disetujui', 'ditolak'])->paginate($perPage);
    }

    public function getLaundryPengajuan()
    {
        $perPage = request()->input('per_page', 10);
        return LaundryPengajuan::whereIn('status', ['disetujui', 'ditolak'])->paginate($perPage);
    }
}
