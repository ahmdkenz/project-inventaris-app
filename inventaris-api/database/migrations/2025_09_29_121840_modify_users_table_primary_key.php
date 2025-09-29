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
        // Buat tabel sementara untuk menyimpan data yang ada
        Schema::create('temp_users', function (Blueprint $table) {
            $table->string('id', 20)->primary(); // ID sebagai string dengan panjang 20
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'staff'])->default('staff');
            $table->string('status')->default('active');
            $table->timestamp('last_login')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        
        // Salin data dari tabel asli ke tabel sementara dengan ID baru
        if (Schema::hasTable('users')) {
            DB::statement("INSERT INTO temp_users (
                id, name, email, email_verified_at, password, role, 
                status, last_login, created_at, updated_at
            ) 
            SELECT 
                CONCAT('USR', LPAD(id, 8, '0')), name, email, email_verified_at, password, role,
                COALESCE(status, 'active'), last_login, created_at, updated_at 
            FROM users");
        }
        
        // Hapus foreign key yang mereferensikan users.id
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        // Drop tabel asli
        Schema::dropIfExists('users');
        
        // Rename tabel sementara ke nama tabel asli
        Schema::rename('temp_users', 'users');
        
        // Ubah tipe kolom user_id di tabel terkait
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('user_id', 20)->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
        
        Schema::table('products', function (Blueprint $table) {
            $table->string('user_id', 20)->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dalam kasus rollback, kita akan kembali ke id numerik (auto-increment)
        // Buat tabel sementara
        Schema::create('temp_users', function (Blueprint $table) {
            $table->id(); // Kembali ke BigInteger auto-increment
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'staff'])->default('staff');
            $table->string('status')->default('active');
            $table->timestamp('last_login')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        
        // Hapus foreign key yang mereferensikan users.id
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        // Drop tabel asli
        Schema::dropIfExists('users');
        
        // Rename tabel sementara ke nama tabel asli
        Schema::rename('temp_users', 'users');
        
        // Kembalikan tipe kolom user_id di tabel terkait
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
        
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
