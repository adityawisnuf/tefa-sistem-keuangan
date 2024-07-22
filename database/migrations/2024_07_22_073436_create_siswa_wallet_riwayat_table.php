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
        Schema::create('siswa_wallet_riwayat', function (Blueprint $table) {
            $table->id();
            $table->foreignId("siswa_wallet_id");
            $table->tinyInteger("tujuan_transaksi");
            $table->double("nominal");
            $table->tinyInteger("tipe_transaksi");
            $table->string("merchant_order_id")->nullable();
            $table->tinyInteger("status");
            $table->timestamps();
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
