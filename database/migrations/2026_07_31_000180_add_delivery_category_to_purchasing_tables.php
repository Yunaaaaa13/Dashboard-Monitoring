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
        $tables = ['purchasing_outstandings', 'purchasing_logs', 'master_pos', 'forecastings'];

        foreach ($tables as $t) {
            if (Schema::hasTable($t) && !Schema::hasColumn($t, 'delivery_category_code')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->string('delivery_category_code', 50)->nullable()->default('LOC')->after('id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['purchasing_outstandings', 'purchasing_logs', 'master_pos', 'forecastings'];

        foreach ($tables as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'delivery_category_code')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->dropColumn('delivery_category_code');
                });
            }
        }
    }
};
