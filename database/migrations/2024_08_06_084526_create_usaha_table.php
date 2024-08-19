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
        Schema::dropIfExists('usaha');

        Schema::create('usaha', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('nama_usaha');
            $table->text('alamat');
            $table->string('no_telepon');
            $table->string('no_rekening');
            $table->double('saldo')->default('0');
            $table->enum('status_buka', ['buka', 'tutup'])->default('tutup');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usaha');
    }
};
