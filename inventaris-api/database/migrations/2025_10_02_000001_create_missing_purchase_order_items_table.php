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
        // Periksa apakah tabel purchase_order_items sudah ada
        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                // Menggunakan string untuk product_id karena tabel products menggunakan varchar(20)
                $table->string('product_id', 20);
                $table->string('product_name', 100);
                $table->integer('quantity');
                $table->decimal('unit_price', 12, 2);
                $table->timestamps();
                
                // Tambahkan foreign key secara manual untuk tipe data yang berbeda
                $table->foreign('product_id')->references('id')->on('products');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};