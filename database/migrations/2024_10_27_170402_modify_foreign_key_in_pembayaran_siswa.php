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
        Schema::table('pembayaran_siswa', function (Blueprint $table) {
            $table->dropForeign('pembayaran_siswa_ibfk_1');
            $table->dropForeign('pembayaran_siswa_ibfk_2');

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('pembayaran_id')->references('id')->on('pembayaran')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_siswa', function (Blueprint $table) {
            //
        });
    }
};
