<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pembayaran_siswa_cicilan', function (Blueprint $table) {
            $table->integer('jumlah_cicilan')->nullable(); // Kolom untuk jumlah cicilan
            $table->date('tanggal_cicilan')->nullable(); // Kolom untuk tanggal cicilan
            $table->decimal('total_cicilan', 15, 2)->nullable(); // Kolom untuk total cicilan
            $table->enum('status', ['ongoing', 'complete'])->default('ongoing'); // Status pembayaran
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pembayaran_siswa_cicilan', function (Blueprint $table) {
            $table->dropColumn(['jumlah_cicilan', 'tanggal_cicilan', 'total_cicilan', 'status']);
        });
    }
};