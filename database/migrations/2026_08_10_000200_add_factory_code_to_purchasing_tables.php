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
        $tables = [
            'purchasing_outstandings',
            'forecastings',
            'master_pos',
            'actual_productions',
            'actuals',
            'outstandings',
            'purchasing_logs',
        ];

        foreach ($tables as $t) {
            if (Schema::hasTable($t) && !Schema::hasColumn($t, 'factory_code')) {
                Schema::table($t, function (Blueprint $table) use ($t) {
                    $column = $table->string('factory_code', 50)->nullable()->default('KIP 1');
                    if (Schema::hasColumn($t, 'part_number')) {
                        $column->after('part_number');
                    } elseif (Schema::hasColumn($t, 'item_code')) {
                        $column->after('item_code');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'purchasing_outstandings',
            'forecastings',
            'master_pos',
            'actual_productions',
            'actuals',
            'outstandings',
            'purchasing_logs',
        ];

        foreach ($tables as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'factory_code')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->dropColumn('factory_code');
                });
            }
        }
    }
};
