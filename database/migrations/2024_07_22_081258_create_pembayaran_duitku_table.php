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
        Schema::create('pembayaran_duitku', function (Blueprint $table) {
            $table->string("merchant_order_id");
            $table->string("reference");
            $table->string("payment_method");
            $table->longtext("transaction_response")->nullabel();
            $table->longtext("callback_response")->nullabel();
            $table->string("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_duitku');
    }
};
