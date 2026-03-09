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
        Schema::table('items', function (Blueprint $table) {
            $table->index('category_id', 'idx_items_category_id');
            $table->index('name', 'idx_items_name');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->index('item_id', 'idx_purchase_items_item_id');
            $table->index('purchase_id', 'idx_purchase_items_purchase_id');
            $table->index(['item_id', 'purchase_id'], 'idx_purchase_items_item_purchase');
        });

        Schema::table('usage_items', function (Blueprint $table) {
            $table->index('item_id', 'idx_usage_items_item_id');
            $table->index('usage_id', 'idx_usage_items_usage_id');
            $table->index(['item_id', 'usage_id'], 'idx_usage_items_item_usage');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->index('purchase_date', 'idx_purchases_purchase_date');
        });

        Schema::table('usages', function (Blueprint $table) {
            $table->index('usage_date', 'idx_usages_usage_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('idx_items_category_id');
            $table->dropIndex('idx_items_name');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropIndex('idx_purchase_items_item_id');
            $table->dropIndex('idx_purchase_items_purchase_id');
            $table->dropIndex('idx_purchase_items_item_purchase');
        });

        Schema::table('usage_items', function (Blueprint $table) {
            $table->dropIndex('idx_usage_items_item_id');
            $table->dropIndex('idx_usage_items_usage_id');
            $table->dropIndex('idx_usage_items_item_usage');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex('idx_purchases_purchase_date');
        });

        Schema::table('usages', function (Blueprint $table) {
            $table->dropIndex('idx_usages_usage_date');
        });
    }
};