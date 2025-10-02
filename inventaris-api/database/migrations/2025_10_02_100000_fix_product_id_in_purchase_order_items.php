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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Hapus dulu foreign key constraint jika ada
            if (Schema::hasColumn('purchase_order_items', 'product_id')) {
                // Cek apakah kolom memiliki foreign key
                $foreignKeys = DB::select(DB::raw('
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.TABLE_CONSTRAINTS
                    WHERE TABLE_NAME = "purchase_order_items"
                    AND CONSTRAINT_TYPE = "FOREIGN KEY"
                    AND CONSTRAINT_NAME LIKE "%product_id%"
                '));
                
                // Hapus foreign key jika ada
                if (!empty($foreignKeys)) {
                    foreach ($foreignKeys as $foreignKey) {
                        $table->dropForeign($foreignKey->CONSTRAINT_NAME);
                    }
                }
                
                // Ubah tipe data
                $table->string('product_id', 20)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->bigInteger('product_id')->unsigned()->change();
        });
    }
};