<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tax_exchange_rates')) {
            Schema::table('tax_exchange_rates', function (Blueprint $table) {
                $table->index(
                    ['currency_code', 'exch_year', 'exch_month', 'week_code'],
                    'tax_rate_currency_year_month_week_idx'
                );
            });
        }

        if (Schema::hasTable('tax_budget_forecast_rates')) {
            Schema::table('tax_budget_forecast_rates', function (Blueprint $table) {
                $table->index(
                    ['currency_code', 'exch_year', 'exch_month'],
                    'tax_budget_currency_year_month_idx'
                );
            });
        }

        if (Schema::hasTable('purchasing_outstandings')) {
            Schema::table('purchasing_outstandings', function (Blueprint $table) {
                $table->index(
                    ['delivery_category_code', 'part_number'],
                    'po_delivery_category_part_idx'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tax_exchange_rates')) {
            Schema::table('tax_exchange_rates', function (Blueprint $table) {
                $table->dropIndex('tax_rate_currency_year_month_week_idx');
            });
        }

        if (Schema::hasTable('tax_budget_forecast_rates')) {
            Schema::table('tax_budget_forecast_rates', function (Blueprint $table) {
                $table->dropIndex('tax_budget_currency_year_month_idx');
            });
        }

        if (Schema::hasTable('purchasing_outstandings')) {
            Schema::table('purchasing_outstandings', function (Blueprint $table) {
                $table->dropIndex('po_delivery_category_part_idx');
            });
        }
    }
};
