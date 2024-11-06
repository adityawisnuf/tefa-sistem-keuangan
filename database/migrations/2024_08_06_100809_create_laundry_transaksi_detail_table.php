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
        Schema::create('laundry_transaksi_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laundry_layanan_id');
            $table->unsignedBigInteger('laundry_transaksi_id');
            $table->unsignedInteger('jumlah');
            $table->unsignedInteger('harga');
            $table->timestamps();

            $table->foreign('laundry_layanan_id')->references('id')->on('laundry_layanan');
            $table->foreign('laundry_transaksi_id')->references('id')->on('laundry_transaksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundry_transaksi_detail');
    }
};
