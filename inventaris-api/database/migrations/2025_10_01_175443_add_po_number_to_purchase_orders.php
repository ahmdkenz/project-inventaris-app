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
            if (!Schema::hasColumn('purchase_orders', 'po_number')) {
                $table->string('po_number', 50)->unique()->after('id');
            }
            
            if (!Schema::hasColumn('purchase_orders', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            
            if (!Schema::hasColumn('purchase_orders', 'notes') && !Schema::hasColumn('purchase_orders', 'rejection_reason')) {
                $table->text('notes')->nullable();
            }
            
            if (!Schema::hasColumn('purchase_orders', 'status')) {
                $table->enum('status', ['pending', 'approved', 'received', 'cancelled'])->default('pending');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['po_number', 'rejection_reason']);
        });
    }
};
