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
        Schema::dropIfExists('kantin_transaksi_detail');

        Schema::create('kantin_transaksi_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kantin_produk_id');
            $table->unsignedBigInteger('kantin_transaksi_id');
            $table->unsignedInteger('jumlah');
            $table->unsignedInteger('harga');
            $table->timestamps();

            $table->foreign('kantin_produk_id')->references('id')->on('kantin_produk');
            $table->foreign('kantin_transaksi_id')->references('id')->on('kantin_transaksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kantin_transaksi_detail');

    }
};
