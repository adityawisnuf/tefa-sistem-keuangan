<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranSiswaCicilan extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_siswa_cicilan';

    protected $fillable = [
        'siswa_id',
        'jumlah_cicilan',
        'tanggal_cicilan',
        'total_cilan',
        'status'
    ];

    public function pembayaran_siswa()
    {
        return $this->belongsTo(PembayaranSiswa::class, 'pembayaran_siswa_id');
    }

    public function pembayaran_duitku()
    {
        return $this->hasOne(PembayaranDuitku::class, 'merchant_order_id', 'merchant_order_id');
    }

      public function cicilan()
      {
          return $this->hasMany(PembayaranCicilan::class, 'pembayaran_siswa_cicilan_id');
      }

      public function siswa()
      {
          return $this->belongsTo(Siswa::class, 'siswa_id');
      }
}
