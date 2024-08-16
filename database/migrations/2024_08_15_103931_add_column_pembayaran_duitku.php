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
        {
            Schema::table('pembayaran_duitku', function (Blueprint $table) {
                $table->longText('data_user_response')->nullable()->after('transaction_response');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_duitku', function (Blueprint $table) {
            $table->dropColumn('data_user_response');
        });
    }
};
