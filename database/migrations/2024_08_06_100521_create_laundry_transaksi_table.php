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
        Schema::dropIfExists('laundry_transaksi');
        Schema::create('laundry_transaksi', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('siswa_id');
            $table->enum('status', ['pending', 'proses', 'siap_diambil', 'selesai', 'dibatalkan'])->default('pending');
            $table->dateTime('tanggal_pemesanan')->default(now());
            $table->dateTime('tanggal_selesai')->nullable();
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundry_transaksi');
    }
};
