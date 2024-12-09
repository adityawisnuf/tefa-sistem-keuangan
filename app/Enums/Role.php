<?php

namespace App\Enums;

enum Role: string
{
    case Bendahara = 'Bendahara';
    case KepalaSekolah = 'KepalaSekolah';
    case Sekolah = 'Sekolah';
    case OrangTua = 'Orang Tua';
    case Siswa = 'Siswa';
}
