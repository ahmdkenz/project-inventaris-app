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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'category')) {
                $table->string('category', 100)->after('sku');
            }
            if (!Schema::hasColumn('products', 'stock')) {
                $table->integer('stock')->default(0)->after('selling_price');
            }
            if (!Schema::hasColumn('products', 'min_stock')) {
                $table->integer('min_stock')->default(10)->after('stock');
            }
            if (!Schema::hasColumn('products', 'status')) {
                $table->string('status', 20)->default('active')->after('min_stock');
            }
            if (!Schema::hasColumn('products', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('products', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('products', 'min_stock')) {
                $table->dropColumn('min_stock');
            }
            if (Schema::hasColumn('products', 'stock')) {
                $table->dropColumn('stock');
            }
            if (Schema::hasColumn('products', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};
