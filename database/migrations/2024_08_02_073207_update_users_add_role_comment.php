<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 255)->default('OrangTua')->comment('Admin; KepalaSekolah; Bendahara; OrangTua; Siswa; Kantin; Laundry;')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_kategori', function (Blueprint $table) {
            $table->string('role', 255)->default('OrangTua')->change();
        });
    }
};
