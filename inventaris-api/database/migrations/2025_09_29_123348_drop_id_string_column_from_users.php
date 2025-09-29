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
        // Hapus kolom id_string jika masih ada
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'id_string')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('id_string');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tambahkan kembali kolom id_string jika diperlukan
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'id_string')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('id_string', 20)->nullable()->after('id');
            });
        }
    }
};
