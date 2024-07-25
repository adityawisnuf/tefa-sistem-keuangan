<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranDuitku extends Model
{
    use HasFactory;

    protected $table= 'pembayaran_duitku';

    protected $fillable = [
        'merchant_order_id', 'reference', 'payment_method', 'transaction_response', 'callback_response', 'status',
    ];

    public function Ppdb() 
    {
        return $this->hasOne(Ppdb::class, 'merchant_order_id');
    }
    public function siswa_wallet_riwayat() 
    {
        return $this->hasOne(SiswaWalletRiwayat::class, 'merchant_order_id');
    }
    public function kantin_transaksi() 
    {
        return $this->hasOne(KantinTransaksi::class, 'merchant_order_id');
    }
    public function laundry_transaksi() 
    {
        return $this->hasOne(LaundryTransaksi::class, 'merchant_order_id');
    }

}
