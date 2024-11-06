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
            $table->unsignedBigInteger('usaha_id');
            $table->unsignedBigInteger('kantin_produk_kategori_id');
            $table->string('nama_produk');
            $table->string('foto_produk');
            $table->text('deskripsi');
            $table->unsignedInteger('harga_pokok');
            $table->unsignedInteger('harga_jual');
            $table->unsignedInteger('stok')->default(0);
            $table->enum('status',['aktif','tidak_aktif'])->default('tidak_aktif');
            $table->softDeletes();

            $table->timestamps();
            $table->foreign('usaha_id')->references('id')->on('usaha');
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
