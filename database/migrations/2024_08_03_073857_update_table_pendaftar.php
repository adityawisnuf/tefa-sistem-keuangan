<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTablePendaftar extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */

    public function up()
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->string('nisn')->nullable()->after('jenis_kelamin'); // Menambahkan kolom nisn
            $table->string('nik')->nullable()->after('jenis_kelamin');  // Menambahkan kolom nik
            $table->string('email')->nullable()->after('nik');  // Menambahkan kolom nik
            $table->timestamp('email_verified_at')->nullable()->after('email');  // Menambahkan
        });
    }

    /**
     * Reverse the migrations.
     *  @return void
     */
    public function down(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->dropColumn('nisn')->after('jenis_kelamin'); // Menghapus kolom nisn
            $table->dropColumn('email')->after('nik');  // Menghapus kolom nik
        });
    }
};
