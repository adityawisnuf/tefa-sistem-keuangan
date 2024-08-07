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
        
        Schema::create('kantin_produk', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kantin_id');
            $table->unsignedBigInteger('kantin_produk_kategori_id');
            $table->string('nama_produk');
            $table->string('foto_produk');
            $table->text('deskripsi');
            $table->unsignedInteger('harga');
            $table->unsignedInteger('stok');
            $table->enum('status',['aktif','tidak_aktif'])->default('tidak_aktif');

            $table->timestamps();
            $table->foreign('kantin_id')->references('id')->on('kantin');
            $table->foreign('kantin_produk_kategori_id')->references('id')->on('kantin_produk_kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kantin_produk');

    }
};
