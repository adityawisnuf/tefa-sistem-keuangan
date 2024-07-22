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
        Schema::create('pendaftaran_akademik', function (Blueprint $table) {
            $table->id();
            $table->integer("ppdb_id");
            $table->string("sekolah_asal")->nullabel();
            $table->dateTime("tahun_lulus")->nullabel();
            $table->string("jurusan_tujuan")->nullabel();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_akademik');
    }
};
