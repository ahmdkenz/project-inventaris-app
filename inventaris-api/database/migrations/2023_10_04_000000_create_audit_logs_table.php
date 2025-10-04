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
            $table->unsignedBigInteger('user_id')->nullable(); // User yang melakukan aksi
            $table->string('action'); // Create, Update, Delete, etc.
            $table->string('model_type'); // Model yang dimodifikasi
            $table->unsignedBigInteger('model_id')->nullable(); // ID dari model yang dimodifikasi
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