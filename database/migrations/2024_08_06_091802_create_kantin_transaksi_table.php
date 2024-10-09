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
        Schema::dropIfExists('kantin_transaksi');

        Schema::create('kantin_transaksi', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('siswa_id');
            $table->unsignedBigInteger('usaha_id');
            $table->enum('status',['pending','proses', 'siap_diambil', 'selesai','dibatalkan'])->default('pending');
            $table->dateTime('tanggal_pemesanan')->useCurrent();
            $table->dateTime('tanggal_selesai')->nullable();
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswa');
            $table->foreign('usaha_id')->references('id')->on('usaha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kantin_transaksi');

    }
};
