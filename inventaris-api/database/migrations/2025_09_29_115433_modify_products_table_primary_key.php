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
        // Buat tabel sementara untuk menyimpan data yang ada
        Schema::create('temp_products', function (Blueprint $table) {
            $table->string('id', 20)->primary(); // ID sebagai string dengan panjang 20
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->unique();
            $table->string('category')->default('Lainnya');
            $table->unsignedBigInteger('category_id')->default(0);
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(10);
            $table->string('status')->default('active');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
        
        // Salin data dari tabel asli ke tabel sementara dengan ID baru
        if (Schema::hasTable('products')) {
            DB::statement("INSERT INTO temp_products (
                id, name, description, sku, category, category_id, 
                purchase_price, selling_price, stock, min_stock, 
                status, user_id, created_at, updated_at
            ) 
            SELECT 
                CONCAT('PRD', LPAD(id, 8, '0')), name, description, sku, category, category_id,
                purchase_price, selling_price, stock, min_stock,
                status, user_id, created_at, updated_at 
            FROM products");
        }
        
        // Drop tabel asli
        Schema::dropIfExists('products');
        
        // Rename tabel sementara ke nama tabel asli
        Schema::rename('temp_products', 'products');
        
        // Buat foreign keys yang diperlukan
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dalam kasus rollback, kita akan kembali ke id numerik (auto-increment)
        Schema::create('temp_products', function (Blueprint $table) {
            $table->id(); // Kembali ke BigInteger auto-increment
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->unique();
            $table->string('category')->default('Lainnya');
            $table->unsignedBigInteger('category_id')->default(0);
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(10);
            $table->string('status')->default('active');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
        
        // Salin data (akan kehilangan ID kustom)
        DB::statement("INSERT INTO temp_products (
            name, description, sku, category, category_id, 
            purchase_price, selling_price, stock, min_stock, 
            status, user_id, created_at, updated_at
        ) 
        SELECT 
            name, description, sku, category, category_id,
            purchase_price, selling_price, stock, min_stock,
            status, user_id, created_at, updated_at 
        FROM products");
        
        // Drop dan rename
        Schema::dropIfExists('products');
        Schema::rename('temp_products', 'products');
        
        // Buat foreign keys yang diperlukan
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
