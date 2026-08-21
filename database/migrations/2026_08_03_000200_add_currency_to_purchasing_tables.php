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
        $tables = ['purchasing_outstandings', 'purchasing_logs', 'master_pos', 'forecastings', 'actual_productions'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'currency')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'price')) {
                        $table->string('currency', 10)->default('USD')->after('price');
                    } else {
                        $table->string('currency', 10)->default('USD');
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
        $tables = ['purchasing_outstandings', 'purchasing_logs', 'master_pos', 'forecastings', 'actual_productions'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'currency')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('currency');
                });
            }
        }
    }
};
