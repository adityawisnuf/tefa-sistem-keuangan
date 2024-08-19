<?php

namespace App\Http\Controllers;

use App\Models\KantinPengajuan;
use App\Models\KantinTransaksi;
use App\Models\LaundryPengajuan;
use App\Models\LaundryTransaksi;
use App\Models\LaundryTransaksiKiloan;
use App\Models\LaundryTransaksiSatuan;
use App\Models\UsahaPengajuan;
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

    public function getLaundryTransaksi()
    {
        $perPage = request()->input('per_page', 10);
        return LaundryTransaksi::whereIn('status', ['dibatalkan', 'selesai'])
            ->whereBetween('tanggal_selesai', [$this->startOfWeek, $this->endOfWeek])
            ->paginate($perPage);
    }



    public function getUsahaPengajuan()
    {
        $perPage = request()->input('per_page', 10);
        return UsahaPengajuan::whereIn('status', ['disetujui', 'ditolak'])->paginate($perPage);
    }


}
