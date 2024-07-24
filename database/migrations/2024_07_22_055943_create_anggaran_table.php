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
        Schema::create('anggaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama_anggaran');
            $table->double('nominal');
            $table->text('deskripsi')->nullable();
            $table->dateTime('tanggal_pengajuan');
            $table->dateTime('target_teralisasikan')->nullable();
            $table->tinyInteger('status')->default(1)->comment("1 = Diajukan; 2 = diapprove; 3 = terealisasikan; 4 = gagal terealisasikan");
            $table->string('pengapprove')->nullable();
            $table->string('pengapprove_jabatan')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggaran');
    }
};
