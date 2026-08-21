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
        // Helper function to safely add an index
        $addIndexSafely = function ($table, array $columns, string $indexName) {
            try {
                Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                    $t->index($columns, $indexName);
                });
            } catch (\Throwable $e) {
                // Ignore if index already exists
            }
        };

        if (Schema::hasTable('purchasing_logs')) {
            if (Schema::hasColumn('purchasing_logs', 'item_code') && Schema::hasColumn('purchasing_logs', 'receipt_date')) {
                $addIndexSafely('purchasing_logs', ['item_code', 'receipt_date'], 'plog_code_date_idx');
            }
            if (Schema::hasColumn('purchasing_logs', 'po_reference') && Schema::hasColumn('purchasing_logs', 'receipt_date')) {
                $addIndexSafely('purchasing_logs', ['po_reference', 'receipt_date'], 'plog_poref_date_idx');
            }
            if (Schema::hasColumn('purchasing_logs', 'purchasing_category_id') && Schema::hasColumn('purchasing_logs', 'period_month')) {
                $addIndexSafely('purchasing_logs', ['purchasing_category_id', 'period_month'], 'plog_cat_month_idx');
            }
        }

        if (Schema::hasTable('actual_productions')) {
            if (Schema::hasColumn('actual_productions', 'item_code') && Schema::hasColumn('actual_productions', 'tanggal_produksi')) {
                $addIndexSafely('actual_productions', ['item_code', 'tanggal_produksi'], 'aprod_code_date_idx');
            }
        }

        if (Schema::hasTable('purchasing_outstandings')) {
            if (Schema::hasColumn('purchasing_outstandings', 'part_number') && Schema::hasColumn('purchasing_outstandings', 'factory_code')) {
                $addIndexSafely('purchasing_outstandings', ['part_number', 'factory_code'], 'po_pn_factory_idx');
            }
            if (Schema::hasColumn('purchasing_outstandings', 'drawing') && Schema::hasColumn('purchasing_outstandings', 'factory_code')) {
                $addIndexSafely('purchasing_outstandings', ['drawing', 'factory_code'], 'po_dwg_factory_idx');
            }
        }

        if (Schema::hasTable('master_pos')) {
            if (Schema::hasColumn('master_pos', 'po') && Schema::hasColumn('master_pos', 'item_code')) {
                $addIndexSafely('master_pos', ['po', 'item_code'], 'mpo_po_item_idx');
            }
            if (Schema::hasColumn('master_pos', 'po') && Schema::hasColumn('master_pos', 'tanggal')) {
                $addIndexSafely('master_pos', ['po', 'tanggal'], 'mpo_po_tanggal_idx');
            }
        }

        if (Schema::hasTable('inventories')) {
            if (Schema::hasColumn('inventories', 'part_number') && Schema::hasColumn('inventories', 'factory_code')) {
                $addIndexSafely('inventories', ['part_number', 'factory_code'], 'inv_pn_factory_idx');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dropIndexSafely = function ($table, string $indexName) {
            try {
                Schema::table($table, function (Blueprint $t) use ($indexName) {
                    $t->dropIndex($indexName);
                });
            } catch (\Throwable $e) {}
        };

        if (Schema::hasTable('purchasing_logs')) {
            $dropIndexSafely('purchasing_logs', 'plog_code_date_idx');
            $dropIndexSafely('purchasing_logs', 'plog_poref_date_idx');
            $dropIndexSafely('purchasing_logs', 'plog_cat_month_idx');
        }

        if (Schema::hasTable('actual_productions')) {
            $dropIndexSafely('actual_productions', 'aprod_code_date_idx');
        }

        if (Schema::hasTable('purchasing_outstandings')) {
            $dropIndexSafely('purchasing_outstandings', 'po_pn_factory_idx');
            $dropIndexSafely('purchasing_outstandings', 'po_dwg_factory_idx');
        }

        if (Schema::hasTable('master_pos')) {
            $dropIndexSafely('master_pos', 'mpo_po_item_idx');
            $dropIndexSafely('master_pos', 'mpo_po_tanggal_idx');
        }

        if (Schema::hasTable('inventories')) {
            $dropIndexSafely('inventories', 'inv_pn_factory_idx');
        }
    }
};
