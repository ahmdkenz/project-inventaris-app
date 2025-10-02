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
        // Cek struktur tabel
        $columns = DB::select("SHOW COLUMNS FROM purchase_orders");
        file_put_contents(storage_path('logs/table_structure.log'), print_r($columns, true));
        
        // Tambahkan kolom yang hilang jika diperlukan
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'order_date')) {
                $table->date('order_date')->after('supplier_id');
            }
            
            if (!Schema::hasColumn('purchase_orders', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Tidak perlu menghapus kolom, ini hanya untuk pemeriksaan
        });
    }
};
