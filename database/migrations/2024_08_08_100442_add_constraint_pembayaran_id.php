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
        Schema::table('pembayaran_ppdb', function (Blueprint $table) {

            // Menambahkan foreign key
            $table->foreign('pembayaran_id')->references('id')->on('pembayaran')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_ppdb', function (Blueprint $table) {

            // Menghapus kolom ppdb_id
            $table->dropColumn('pembayaran_id');
        });
    }
};
