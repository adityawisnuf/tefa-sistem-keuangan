<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiswaWalletRiwayat extends Model
{
    use HasFactory;

    protected $table= 'siswa_wallet_riwayat';

    protected $fillable = [
        'siswa_wallet_id', 'tujuan_transaksi', 'nominal', 'tipe_transaksi', 'merchant_order_id', 'status',
    ];

    public function pembayaran_duitku()
    {
        return $this->belongsTo(PembayaranDuitku::class, 'merchant_order_id');
    }
    public function siswa_wallet()
    {
        return $this->belongsTo(Siswa::class, 'siswa_wallet_id');
    }
}
