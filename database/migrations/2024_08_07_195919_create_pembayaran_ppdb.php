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
        Schema::create('pembayaran_ppdb', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->integer('ppdb_id');
            $table->integer('pembayaran_id');
            $table->double('nominal', 10,2)->default(0);
            $table->string('merchant_order_id', 255)->nullable();
            $table->tinyInteger('status')->comment('1=selesai; 0=belum selesai;');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_ppdb');
    }
};
