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
<<<<<<< HEAD:database/migrations/2024_08_07_122729_update_aset_add_nominal_and_type.php
        Schema::table('aset', function (Blueprint $table) {
            $table->string('tipe', 255)->after('id')->nullable();
            $table->double('harga', 10, 2)->after('nama')->default(0);
=======
        Schema::table('anggaran', function (Blueprint $table) {
            $table->integer('nominal_diapprove')->nullable()->after('nominal');
>>>>>>> 06c349edbca295a5c90b039e83feff5ff13cfa67:database/migrations/2024_08_27_070414_add_nominal_diapprove_column.php
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anggaran', function (Blueprint $table) {
            $table->dropColumn('nominal_diapprove');
        });
    }
};
