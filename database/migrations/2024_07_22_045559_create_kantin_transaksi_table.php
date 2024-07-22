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
        Schema::create('kantin_transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kantin_id');
            $table->integer('qty');
            $table->double('total_harga', 8, 2);
            $table->string('merchant_order_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kantin_transaksi');
    }
};
