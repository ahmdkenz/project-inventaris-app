<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambahkan kolom id_string pada tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->string('id_string', 20)->nullable()->after('id');
        });

        // Update kolom id_string dengan format yang diinginkan
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $userId = 'USR-' . substr(time(), -6) . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            DB::table('users')
                ->where('id', $user->id)
                ->update(['id_string' => $userId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id_string');
        });
    }
};
