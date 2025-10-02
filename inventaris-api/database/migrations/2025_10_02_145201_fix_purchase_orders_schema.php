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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Menambahkan kolom-kolom yang diperlukan jika belum ada
            if (!Schema::hasColumn('purchase_orders', 'order_date')) {
                $table->date('order_date')->after('supplier_id')->nullable();
            }
            
            if (!Schema::hasColumn('purchase_orders', 'expected_delivery')) {
                $table->date('expected_delivery')->after('order_date')->nullable();
            }
            
            if (!Schema::hasColumn('purchase_orders', 'notes')) {
                $table->text('notes')->after('status')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Tidak perlu menghapus kolom karena ini adalah perbaikan skema
        });
    }
};
