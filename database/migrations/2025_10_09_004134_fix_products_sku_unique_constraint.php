<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fix: Remove global unique constraint from SKU column
     * and add composite unique constraint (seller_id, sku)
     * to allow different sellers to use the same SKU.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop existing unique constraint on sku column
            $table->dropUnique(['sku']);

            // Add composite unique constraint: seller can't have duplicate SKUs
            $table->unique(['seller_id', 'sku'], 'products_seller_id_sku_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop composite unique constraint
            $table->dropUnique('products_seller_id_sku_unique');

            // Restore global unique constraint
            $table->unique('sku');
        });
    }
};
