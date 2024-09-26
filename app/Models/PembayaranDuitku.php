<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranDuitku extends Model
{
    use HasFactory;

    // Tentukan nama tabel
    protected $table = 'pembayaran_duitku';

    // Merchant order ID adalah primary key
    protected $primaryKey = 'merchant_order_id';
    public $incrementing = false;  // Jika 'merchant_order_id' bukan auto-increment

    // Tentukan kolom yang bisa diisi
    protected $fillable = [
        'merchant_order_id',
        'reference',
        'payment_method',
        'transaction_response',
        'callback_response',
        'status',
    ];

    // Jika ada kolom JSON seperti 'transaction_response' dan 'callback_response'
    protected $casts = [
        'transaction_response' => 'array',
        'callback_response' => 'array',
    ];

    // Relasi ke tabel ppdb
    public function ppdb()
    {
        return $this->hasOne(Ppdb::class, 'merchant_order_id', 'merchant_order_id');
    }

    // Relasi ke tabel siswa_wallet_riwayat
    public function siswaWalletRiwayat()
    {
        return $this->hasOne(SiswaWalletRiwayat::class, 'merchant_order_id', 'merchant_order_id');
    }

    // Relasi ke tabel kantin_transaksi
    public function kantinTransaksi()
    {
        return $this->hasOne(KantinTransaksi::class, 'merchant_order_id', 'merchant_order_id');
    }

    // Relasi ke tabel laundry_transaksi
    public function laundryTransaksi()
    {
        return $this->hasOne(LaundryTransaksi::class, 'merchant_order_id', 'merchant_order_id');
    }

    // Relasi ke tabel pembayaran_siswa
    public function pembayaranSiswa()
    {
        return $this->hasOne(PembayaranSiswa::class, 'merchant_order_id', 'merchant_order_id');
    }

    // Relasi ke tabel pembayaran_siswa_cicilan
    public function pembayaranSiswaCicilan()
    {
        return $this->hasOne(PembayaranSiswaCicilan::class, 'merchant_order_id', 'merchant_order_id');
    }
}
