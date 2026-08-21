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
        if (Schema::hasTable('forecastings') && !Schema::hasColumn('forecastings', 'supplier_name')) {
            Schema::table('forecastings', function (Blueprint $table) {
                $table->string('supplier_name')->nullable()->after('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('forecastings') && Schema::hasColumn('forecastings', 'supplier_name')) {
            Schema::table('forecastings', function (Blueprint $table) {
                $table->dropColumn('supplier_name');
            });
        }
    }
};
