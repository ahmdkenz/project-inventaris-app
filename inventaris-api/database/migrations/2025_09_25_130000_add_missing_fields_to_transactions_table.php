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
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'old_stock')) {
                $table->integer('old_stock')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('transactions', 'new_stock')) {
                $table->integer('new_stock')->nullable()->after('old_stock');
            }
            if (!Schema::hasColumn('transactions', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('new_stock');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'old_stock')) {
                $table->dropColumn('old_stock');
            }
            if (Schema::hasColumn('transactions', 'new_stock')) {
                $table->dropColumn('new_stock');
            }
            if (Schema::hasColumn('transactions', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};