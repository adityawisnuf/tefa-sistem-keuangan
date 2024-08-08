<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ppdb extends Model
{
    use HasFactory;

    protected $table= 'ppdb';

    protected $fillable= [
        'dokumen_pendaftar_id', 'status', 'merchant_order_id',
    ];

    public function pembayaran_duitku()
    {
        return $this->belongsTo(PembayaranDuitku::class, 'merchant_order_id');
    }

    public function pendaftaran_akademik()
    {
        return $this->hasOne(PendaftarAkademik::class, 'ppdb_id');
    }
    public function pendaftar()
    {
        return $this->hasOne(Pendaftar::class, 'ppdb_id');
    }
    public function pendaftar_dokumen()
    {
        return $this->hasOne(PendaftarDokumen::class, 'ppdb_id');
    }

    public function pembayaran_ppdb()
    {
        return $this->hasOne(PembayaranPpdb::class, 'ppdb_id');
    }

}
