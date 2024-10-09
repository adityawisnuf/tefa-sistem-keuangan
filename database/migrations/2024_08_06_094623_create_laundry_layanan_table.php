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
        Schema::create('laundry_layanan', function (Blueprint $table) {
          $table->id();
            $table->unsignedBigInteger('usaha_id');
            $table->string('nama_layanan');
            $table->string('foto_layanan');
            $table->text('deskripsi');
            $table->unsignedInteger('harga');
            $table->enum('tipe',['satuan','kiloan']);
            $table->enum('satuan',['pcs','kg']);
            $table->enum('status',['aktif','tidak_aktif'])->default('tidak_aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('usaha_id')->references('id')->on('usaha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundry_layanan');
    }
};