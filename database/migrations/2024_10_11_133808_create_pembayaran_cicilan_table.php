<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembayaranCicilanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pembayaran_cicilan', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('pembayaran_siswa_cicilan_id'); // Foreign key ke tabel pembayaran_siswa_cicilan
            $table->date('tanggal_pembayaran'); // Tanggal pembayaran cicilan
            $table->decimal('nominal_dibayar', 15, 2); // Nominal yang dibayar
            $table->enum('status', ['lunas', 'belum_lunas'])->default('belum_lunas'); // Status pembayaran
            $table->text('transaction_response'); // Diambil dari tabel pembayaran_duitku
            $table->string('payment_method'); // Diambil dari tabel pembayaran_duitku
            $table->timestamps(); // Timestamps: created_at dan updated_at

            // Foreign key ke tabel pembayaran_siswa_cicilan
            $table->foreign('pembayaran_siswa_cicilan_id')->references('id')->on('pembayaran_siswa_cicilan')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pembayaran_cicilan');
    }
}
