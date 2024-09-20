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
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('status')->default(1); // 1=diajukan, 2=diapprove, 3=direject
            $table->string('judul');
            $table->text('isi');
            $table->text('pesan_ditolak')->nullable();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
