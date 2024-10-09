<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ppdb', function (Blueprint $table) {
            // Tambahkan kolom user_id tanpa auto_increment atau panjang tertentu
            $table->bigInteger('user_id')->after('id')->nullable()->unsigned();

            // Tambahkan foreign key constraint pada user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Ubah kolom status untuk menyertakan nilai 4
            $table->tinyInteger('status')->default(1)->comment('1=mendaftar; 2=telah membayar; 3=telah terdaftar; 4=ditolak')->change();
        });
    }

    public function down()
    {
        Schema::table('ppdb', function (Blueprint $table) {
            // Kembalikan perubahan jika perlu rollback
            $table->dropColumn('user_id');
            $table->dropForeign(['user_id']);
            
            // Kembalikan kolom status ke keadaan sebelumnya
            $table->tinyInteger('status')->default(1)->comment('1=mendaftar; 2=telah membayar; 3=telah terdaftar')->change();
        });
    }

};
