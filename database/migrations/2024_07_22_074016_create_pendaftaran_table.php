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
        Schema::create('pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->integer("ppdb_id");
            $table->string("nama_depan");
            $table->string("nama_belakang")->nullable();
            $table->tinyInteger("jenis_kelamin");
            $table->string("tempat_lahir");
            $table->datetime("tgl_lahir");
            $table->text("alamat");
            $table->bigInteger("village_id");
            $table->string("nama_ayah");
            $table->string("nama_ibu");
            $table->datetime("tgl_lahir_ayah");
            $table->datetime("tgl_lahir_ibu");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran');
    }
};
