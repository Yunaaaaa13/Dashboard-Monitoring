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
        // 1. Forecastings Table Indexes
        if (Schema::hasTable('forecastings')) {
            Schema::table('forecastings', function (Blueprint $table) {
                $table->index('part_number', 'fc_part_number_idx');
                $table->index('periode', 'fc_periode_idx');
                $table->index('period_month', 'fc_period_month_idx');
                $table->index(['part_number', 'periode'], 'fc_pn_periode_idx');
                $table->index(['part_number', 'period_month'], 'fc_pn_pmonth_idx');
            });
        }

        // 2. Purchasing Outstandings Table Indexes
        if (Schema::hasTable('purchasing_outstandings')) {
            Schema::table('purchasing_outstandings', function (Blueprint $table) {
                $table->index('status', 'po_status_idx');
                $table->index('factory_code', 'po_factory_idx');
                $table->index('category_id', 'po_category_idx');
                $table->index('user_id', 'po_user_idx');
            });
        }

        // 3. Master Pos Table Indexes
        if (Schema::hasTable('master_pos')) {
            Schema::table('master_pos', function (Blueprint $table) {
                $table->index('item_code', 'mpo_item_code_idx');
                $table->index('po', 'mpo_po_idx');
                $table->index('tanggal', 'mpo_tanggal_idx');
                $table->index(['item_code', 'tanggal'], 'mpo_item_tanggal_idx');
            });
        }

        // 4. Purchasing Logs Table Indexes
        if (Schema::hasTable('purchasing_logs')) {
            Schema::table('purchasing_logs', function (Blueprint $table) {
                $table->index('period_month', 'plog_period_month_idx');
                $table->index('receipt_date', 'plog_receipt_date_idx');
                $table->index('purchasing_category_id', 'plog_category_idx');
            });
        }

        // 5. Actual Productions Table Indexes
        if (Schema::hasTable('actual_productions')) {
            Schema::table('actual_productions', function (Blueprint $table) {
                $table->index('tanggal_produksi', 'aprod_tanggal_idx');
            });
        }

        // 6. Actuals Table Indexes
        if (Schema::hasTable('actuals')) {
            Schema::table('actuals', function (Blueprint $table) {
                $table->index('part_number', 'act_part_number_idx');
                $table->index('periode', 'act_periode_idx');
                $table->index('period_month', 'act_period_month_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('forecastings')) {
            Schema::table('forecastings', function (Blueprint $table) {
                $table->dropIndex('fc_part_number_idx');
                $table->dropIndex('fc_periode_idx');
                $table->dropIndex('fc_period_month_idx');
                $table->dropIndex('fc_pn_periode_idx');
                $table->dropIndex('fc_pn_pmonth_idx');
            });
        }

        if (Schema::hasTable('purchasing_outstandings')) {
            Schema::table('purchasing_outstandings', function (Blueprint $table) {
                $table->dropIndex('po_status_idx');
                $table->dropIndex('po_factory_idx');
                $table->dropIndex('po_category_idx');
                $table->dropIndex('po_user_idx');
            });
        }

        if (Schema::hasTable('master_pos')) {
            Schema::table('master_pos', function (Blueprint $table) {
                $table->dropIndex('mpo_item_code_idx');
                $table->dropIndex('mpo_po_idx');
                $table->dropIndex('mpo_tanggal_idx');
                $table->dropIndex('mpo_item_tanggal_idx');
            });
        }

        if (Schema::hasTable('purchasing_logs')) {
            Schema::table('purchasing_logs', function (Blueprint $table) {
                $table->dropIndex('plog_period_month_idx');
                $table->dropIndex('plog_receipt_date_idx');
                $table->dropIndex('plog_category_idx');
            });
        }

        if (Schema::hasTable('actual_productions')) {
            Schema::table('actual_productions', function (Blueprint $table) {
                $table->dropIndex('aprod_tanggal_idx');
            });
        }

        if (Schema::hasTable('actuals')) {
            Schema::table('actuals', function (Blueprint $table) {
                $table->dropIndex('act_part_number_idx');
                $table->dropIndex('act_periode_idx');
                $table->dropIndex('act_period_month_idx');
            });
        }
    }
};
