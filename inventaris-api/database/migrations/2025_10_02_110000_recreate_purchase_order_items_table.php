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
        // Membuat tabel baru dengan skema yang benar
        Schema::create('purchase_order_items_v2', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->string('product_id', 20);
            $table->string('product_name', 100);
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->timestamps();
            
            // Tambahkan foreign key secara manual untuk product_id
            $table->foreign('product_id')->references('id')->on('products');
        });
        
        // Pindahkan data jika ada
        if (Schema::hasTable('purchase_order_items')) {
            try {
                $items = DB::table('purchase_order_items')->get();
                foreach ($items as $item) {
                    // Konversi product_id ke string
                    $productId = (string)$item->product_id;
                    
                    DB::table('purchase_order_items_v2')->insert([
                        'purchase_order_id' => $item->purchase_order_id,
                        'product_id' => $productId,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at
                    ]);
                }
                
                // Hapus tabel lama
                Schema::dropIfExists('purchase_order_items');
                
                // Rename tabel baru ke nama lama
                Schema::rename('purchase_order_items_v2', 'purchase_order_items');
            } catch (\Exception $e) {
                // Log error tapi tetap lanjutkan
                \Log::error('Gagal memindahkan data purchase_order_items: ' . $e->getMessage());
                
                // Hapus tabel lama jika ada
                Schema::dropIfExists('purchase_order_items');
                
                // Rename tabel baru ke nama yang benar
                Schema::rename('purchase_order_items_v2', 'purchase_order_items');
            }
        } else {
            // Rename tabel baru ke nama yang benar jika tabel lama tidak ada
            Schema::rename('purchase_order_items_v2', 'purchase_order_items');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Buat skema lama kembali jika dibutuhkan rollback
        Schema::create('purchase_order_items_old', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id');
            $table->string('product_name', 100);
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->timestamps();
        });
        
        Schema::dropIfExists('purchase_order_items');
        Schema::rename('purchase_order_items_old', 'purchase_order_items');
    }
};