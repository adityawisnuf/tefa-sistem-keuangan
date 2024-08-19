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
        Schema::create('usaha_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usaha_id');
            $table->unsignedInteger('jumlah_pengajuan');
            $table->enum('status',['pending','disetujui','ditolak'])->default('pending');
            $table->string('alasan_penolakan')->nullable();
            $table->dateTime('tanggal_pengajuan')->default(now());
            $table->dateTime('tanggal_selesai')->nullable();
            $table->timestamps();

            $table->foreign('usaha_id')->references('id')->on('usaha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usaha_pengajuan');
    }
};
