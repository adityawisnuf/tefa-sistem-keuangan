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
        Schema::create('pembayaran_siswa_cicilan', function (Blueprint $table) {
            $table->id();
            $table->integer("	pembayaran_siswa_id");
            $table->double("nominal_cicilan");
            $table->string("merchant_order_id")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_siswa_cicilan');
    }
};
