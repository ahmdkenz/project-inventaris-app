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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 20)->nullable(); // User yang melakukan aksi (string untuk kompatibilitas dengan users.id)
            $table->string('action'); // Create, Update, Delete, etc.
            $table->string('model_type'); // Model yang dimodifikasi
            $table->string('model_id', 20)->nullable(); // ID dari model yang dimodifikasi (string untuk kompatibilitas)
            $table->text('old_values')->nullable(); // Nilai sebelum perubahan (JSON)
            $table->text('new_values')->nullable(); // Nilai setelah perubahan (JSON)
            $table->string('url')->nullable(); // URL yang memicu aksi
            $table->string('ip_address')->nullable(); // IP address user
            $table->string('user_agent')->nullable(); // Browser user agent
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};