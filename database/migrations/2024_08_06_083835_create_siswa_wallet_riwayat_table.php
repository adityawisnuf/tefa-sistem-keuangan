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
        Schema::dropIfExists('siswa_wallet_riwayat');

        Schema::create('siswa_wallet_riwayat', function (Blueprint $table) {
            $table->id();
            $table->integer('siswa_wallet_id');
            $table->string('merchant_order_id');
            $table->enum('tipe_transaksi', ['pemasukan', 'pengeluaran']);
            $table->double('nominal');
            $table->dateTime('tanggal_riwayat')->default(now());
            $table->timestamps();

            $table->foreign('siswa_wallet_id')->references('id')->on('siswa_wallet');
            $table->foreign('merchant_order_id')->references('merchant_order_id')->on('pembayaran_duitku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa_wallet_riwayat');
    }
};
