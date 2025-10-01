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
            // Pastikan kolom po_number memiliki indeks unik
            if (!Schema::hasColumn('purchase_orders', 'po_number')) {
                $table->string('po_number', 50)->unique()->after('id');
            } else {
                // Jika kolom sudah ada, tambahkan indeks unik
                $table->unique('po_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropUnique(['po_number']);
        });
    }
};
