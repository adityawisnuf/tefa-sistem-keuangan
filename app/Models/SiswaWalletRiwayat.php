<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiswaWalletRiwayat extends Model
{
    use HasFactory;

    protected $table = 'siswa_wallet_riwayat';

    protected $fillable = [
        'siswa_wallet_id',
        'merchant_order_id',
        'tipe_transaksi',
        'nominal',
        'tanggal_riwayat',
    ];

    public function siswa_wallet()
    {
        return $this->belongsTo(SiswaWallet::class, 'siswa_wallet_id');
    }

    public function pembayaran_duitku()
    {
        return $this->belongsTo(PembayaranDuitku::class, 'merchant_order_id');
    }
}
