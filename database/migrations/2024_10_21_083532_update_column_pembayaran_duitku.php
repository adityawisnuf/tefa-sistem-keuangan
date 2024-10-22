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
        Schema::table('pembayaran_duitku', function (Blueprint $table) {
            $table->string('reference')->nullable()->change();
            $table->string('payment_method')->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_duitku', function (Blueprint $table) {
            $table->string('reference')->nullable(false)->change();
            $table->string('payment_method')->nullable(false)->change();

        });
    }
};
