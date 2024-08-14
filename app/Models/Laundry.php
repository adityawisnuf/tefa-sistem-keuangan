<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laundry extends Model
{
    use HasFactory;

    protected $table = 'laundry';

    protected $fillable = [
        'user_id',
        'nama_laundry',
        'alamat',
        'no_telepon',
        'no_rekening',
        'saldo',
        'status_buka',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function laundry_layanan()
    {
        return $this->hasMany(LaundryLayanan::class, 'laundry_id');
    }

    public function laundry_item()
    {
        return $this->hasMany(LaundryItem::class, 'laundry_id');
    }

    public function laundry_pengajuan()
    {
        return $this->hasMany(LaundryPengajuan::class, 'laundry_id');
    }

    public function laundry_transaksi_kiloan()
    {
        return $this->hasMany(LaundryTransaksiKiloan::class, 'laundry_id');
    }

    public function laundry_transaksi_satuan()
    {
        return $this->hasMany(LaundryTransaksiSatuan::class, 'laundry_id');
    }
}
