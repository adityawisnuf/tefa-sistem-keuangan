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
        Schema::create('pendaftaran_dokumen', function (Blueprint $table) {
            $table->id();
            $table->integer("ppdb_id");
            $table->text("akte_kelahiran")->nullabel();
            $table->text("kartu_keluarga")->nullabel();
            $table->text("ijazah")->nullabel();
            $table->text("raport")->nullabel();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_dokumen');
    }
};
