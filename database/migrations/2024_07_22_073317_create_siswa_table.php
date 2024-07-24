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
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("user_id");
            $table->string("nama_depan");
            $table->string("nama_belakang")->nullable();
            $table->text("alamat")->nullable();
            $table->string("tempat_lahir");
            $table->date("tanggal_lahir");
            $table->string("telepon")->nullable();
            $table->bigInteger("kelas_id");
            $table->integer("orangtua_id")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
