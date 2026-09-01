<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('forecastings')) {
            try {
                Schema::table('forecastings', function (Blueprint $table) {
                    $table->dropUnique('forecastings_part_number_period_month_unique');
                });
            } catch (\Throwable $e) {
                // Index already dropped or doesn't exist
            }

            try {
                Schema::table('forecastings', function (Blueprint $table) {
                    $table->index(['part_number', 'factory_code', 'period_month'], 'fc_pn_factory_period_idx');
                });
            } catch (\Throwable $e) {
                // Index already exists
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('forecastings')) {
            try {
                Schema::table('forecastings', function (Blueprint $table) {
                    $table->dropIndex('fc_pn_factory_period_idx');
                });
            } catch (\Throwable $e) {
                //
            }
        }
    }
};
