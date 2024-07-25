<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranSiswa extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_siswa';

    protected $fillable = [
        'siswa_id', 
        'pembayaran_kategori_id', 
        'nominal', 
        'merchant_code', 
        'status',
    ];

    public function Siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function PembayaranKategori()
    {
        return $this->belongsTo(PembayaranKategori::class, 'pembayaran_kategori_id');
    }

    public function PembayaranDuitku()
    {
        return $this->belongsTo(PembayaranDuitku::class, 'pembayaran_duitku_id');
    }

    public function PembayaranSiswaCicilan()
    {
        return $this->hasMany(PembayaranSiswaCicilan::class, 'pembayaran_siswa_id');
    }
}
