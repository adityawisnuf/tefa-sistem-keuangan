<?php

namespace App\Enums;

enum Role: string
{
    case Bendahara = 'Bendahara';
    case KepalaSekolah = 'Kepala Sekolah';
    case Sekolah = 'Sekolah';
    case OrangTua = 'Orang Tua';
    case Siswa = 'Siswa';
}
