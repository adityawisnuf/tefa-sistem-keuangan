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
        Schema::create('laundry_item', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laundry_id');
            $table->string('nama_item');
            $table->string('foto_item');
            $table->text('deskripsi');
            $table->unsignedInteger('harga');
            $table->enum('status', ['aktif', 'tidak_aktif']);
            $table->timestamps();

            $table->foreign('laundry_id')->references('id')->on('laundry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundry_item');
    }
};
