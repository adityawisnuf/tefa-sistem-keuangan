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
        Schema::create('laundry_transaksi_satuan_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laundry_item_satuan_id');
            $table->unsignedBigInteger('laundry_transaksi_satuan_id');
            $table->unsignedInteger('jumlah');
            $table->unsignedInteger('harga');
            $table->unsignedInteger('harga_total');
            $table->timestamps();

            $table->foreign('laundry_item_satuan_id')->references('id')->on('laundry_item_satuan');
            $table->foreign('laundry_transaksi_satuan_id')->references('id')->on('laundry_transaksi_satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundry_transaksi_satuan_detail');
    }
};
