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
            $table->foreign('merchant_order_id')->references('merchant_order_id')->on('pembayaran_duitku')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_ppdb', function (Blueprint $table) {

            // Menghapus kolom ppdb_id
            $table->dropColumn('merchant_order_id');
        });
    }
};
