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
        Schema::create('asset', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kategori');
            $table->date('tanggal_pembelian');
            $table->decimal('harga');
            $table->text('keterangan')->nullable();
            $table->integer('jumlah');
            $table->integer('kondisi_baik')->default(0);
            $table->integer('kondisi_kurang_baik')->default(0);
            $table->integer('kondisi_buruk')->default(0);
            $table->string('penggunaan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset');
    }
};
