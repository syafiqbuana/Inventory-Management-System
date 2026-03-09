<?php
// File: database/migrations/2026_03_09_optimize_indexes.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. ANALYZE TABLES (Update statistics)
        DB::statement('ANALYZE TABLE periods');
        DB::statement('ANALYZE TABLE items');
        DB::statement('ANALYZE TABLE purchase_items');
        DB::statement('ANALYZE TABLE purchases');
        DB::statement('ANALYZE TABLE usage_items');
        DB::statement('ANALYZE TABLE usages');
        DB::statement('ANALYZE TABLE categories');
        DB::statement('ANALYZE TABLE item_types');

        // 2. REMOVE DUPLICATE INDEXES
        Schema::table('items', function ($table) {
            $table->dropIndex('idx_items_category_id');
        });

        Schema::table('purchase_items', function ($table) {
            $table->dropIndex('idx_purchase_items_item_id');
            $table->dropIndex('idx_purchase_items_purchase_id');
        });

        Schema::table('usage_items', function ($table) {
            $table->dropIndex('idx_usage_items_item_id');
            $table->dropIndex('idx_usage_items_usage_id');
        });

        // 3. ADD NEW COMPOSITE INDEXES
        Schema::table('purchases', function ($table) {
            $table->index(['period_id', 'purchase_date'], 'idx_purchases_period_date');
        });

        Schema::table('usages', function ($table) {
            $table->index(['period_id', 'usage_date'], 'idx_usages_period_date');
        });

        // 4. OPTIMIZE TABLES
        DB::statement('OPTIMIZE TABLE items');
        DB::statement('OPTIMIZE TABLE purchase_items');
        DB::statement('OPTIMIZE TABLE purchases');
        DB::statement('OPTIMIZE TABLE usage_items');
        DB::statement('OPTIMIZE TABLE usages');
    }

    public function down()
    {
        // Rollback
        Schema::table('items', function ($table) {
            $table->index('category_id', 'idx_items_category_id');
        });

        Schema::table('purchase_items', function ($table) {
            $table->index('item_id', 'idx_purchase_items_item_id');
            $table->index('purchase_id', 'idx_purchase_items_purchase_id');
        });

        Schema::table('usage_items', function ($table) {
            $table->index('item_id', 'idx_usage_items_item_id');
            $table->index('usage_id', 'idx_usage_items_usage_id');
        });

        Schema::table('purchases', function ($table) {
            $table->dropIndex('idx_purchases_period_date');
        });

        Schema::table('usages', function ($table) {
            $table->dropIndex('idx_usages_period_date');
        });
    }
};