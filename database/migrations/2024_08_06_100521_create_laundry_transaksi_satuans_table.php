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
        Schema::create('laundry_transaksi_satuan', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('siswa_id');
            $table->unsignedInteger('jumlah_item');
            $table->unsignedInteger('harga_total');
            $table->enum('status', ['pending', 'proses', 'siap_diambil', 'selesai', 'dibatalkan']);
            $table->dateTime('tanggal_pemesanan');
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
        Schema::dropIfExists('laundry_transaksi_satuan');
    }
};
