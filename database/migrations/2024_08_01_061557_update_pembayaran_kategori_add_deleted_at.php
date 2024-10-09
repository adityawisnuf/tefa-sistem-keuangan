<!-- #region --><?php

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
        Schema::table('pembayaran_kategori', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->default(null)->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_kategori', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
