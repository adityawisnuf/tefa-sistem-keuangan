<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggaran extends Model
{
    use HasFactory;

    protected $table = 'anggaran';

    protected $fillable = [
        'nama_anggaran', 'nominal', 'deskripsi', 'tanggal_pengajuan', 'target_terealisasikan', 'status', 'pengapprove', 'pengapprove_jabatan',  'nominal_diapprove', 'catatan'
    ];

    public function getStatusText()
    {   
        switch ($this->status) {
            case 1:
                return 'Diajukan';
            case 2:
                return 'Disetujui';
            case 3:
                return 'Terealisasikan';
            case 4:
                return 'Gagal Terealisasikan';
            default:
                return 'Status tidak ditemukan';
        }
    }

}
  