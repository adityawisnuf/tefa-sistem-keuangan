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
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropForeign('siswa_ibfk_1');
            $table->dropForeign('siswa_ibfk_2');
            $table->dropForeign('siswa_ibfk_3');
            $table->dropForeign('siswa_ibfk_4');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('orangtua_id')->references('id')->on('orangtua')->onDelete('cascade');

            $table->bigInteger('village_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            
        });
    }
};
