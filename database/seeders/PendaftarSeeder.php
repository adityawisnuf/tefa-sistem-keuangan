<?php

namespace Database\Seeders;

use App\Models\Pendaftar;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PendaftarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pendaftar::create([
            'ppdb_id' => 1,
            'nama_depan' => 'Salman',
            'nama_belakang' => 'Alfarisi',
            'jenis_kelamin' => 1,
            'tempat_lahir' => 'Jakarta',
            'tgl_lahir' => '2005-01-01',
            'alamat' => 'Jl. Merdeka No. 123',
            'village_id' => 1101010004,
            'nama_ayah' => 'Ucup',
            'nama_ibu' => 'Dewi',
            'tgl_lahir_ayah' => '1975-03-15',
            'tgl_lahir_ibu' => '1978-06-20',
        ]);

        Pendaftar::create([
            'ppdb_id' => 1,
            'nama_depan' => 'Akmal',
            'nama_belakang' => 'Saban',
            'jenis_kelamin' => 1,
            'tempat_lahir' => 'Jakarta',
            'tgl_lahir' => '2006-02-14',
            'alamat' => 'Jl. Pahlawan No. 456',
            'village_id' => 1101010004,
            'nama_ayah' => 'Sabni',
            'nama_ibu' => 'Lita',
            'tgl_lahir_ayah' => '1974-12-05',
            'tgl_lahir_ibu' => '1977-08-10',
        ]);

        Pendaftar::create([
            'ppdb_id' => 1,
            'nama_depan' => 'Zahra',
            'nama_belakang' => 'Fatmawati',
            'jenis_kelamin' => 2,
            'tempat_lahir' => 'Bandung',
            'tgl_lahir' => '2006-02-14',
            'alamat' => 'Jl. Pahlawan No. 456',
            'village_id' => 1101010004,
            'nama_ayah' => 'Budi',
            'nama_ibu' => 'Ayu',
            'tgl_lahir_ayah' => '1974-12-05',
            'tgl_lahir_ibu' => '1977-08-10',
        ]);
    }
}
