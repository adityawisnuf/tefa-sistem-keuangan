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
        Schema::create('pembayaran_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId("siswa_id");
            $table->integer("pembayaran_id");
            $table->double("nominal");
            $table->string("merchant_order_id")->nullable();
            $table->tinyInteger("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_siswa');
    }
};
