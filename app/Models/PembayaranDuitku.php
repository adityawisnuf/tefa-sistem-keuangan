<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranDuitku extends Model
{
    use HasFactory;

    protected $table= 'pembayaran_duitku';
    protected $primaryKey = 'merchant_order_id';

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
    public function siswa_wallet_riwayat()
    {
        return $this->hasOne(SiswaWalletRiwayat::class, 'merchant_order_id', 'merchant_order_id');
    }
    public function kantin_transaksi()
    {
        return $this->hasOne(KantinTransaksi::class, 'merchant_order_id', 'merchant_order_id');
    }
    public function laundry_transaksi()
    {
        return $this->hasOne(LaundryTransaksi::class, 'merchant_order_id', 'merchant_order_id');
    }

    public function pembayaran_siswa()
        {
            return $this->hasOne(PembayaranSiswa::class, 'merchant_order_id');
        }
    public function pembayaran_siswa_cicilan()
        {
            return $this->hasOne(PembayaranSiswaCicilan::class, 'merchant_order_id');
        }
    }
