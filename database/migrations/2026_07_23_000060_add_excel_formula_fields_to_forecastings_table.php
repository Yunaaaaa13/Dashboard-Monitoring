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
        Schema::table('forecastings', function (Blueprint $table) {
            if (!Schema::hasColumn('forecastings', 'outstanding_pre')) {
                $table->integer('outstanding_pre')->default(0)->after('description');
            }
            if (!Schema::hasColumn('forecastings', 'stock_pre')) {
                $table->integer('stock_pre')->default(0)->after('outstanding_pre');
            }
            if (!Schema::hasColumn('forecastings', 'delivery')) {
                $table->integer('delivery')->default(0)->after('po');
            }
            if (!Schema::hasColumn('forecastings', 'outstanding')) {
                $table->integer('outstanding')->default(0)->after('delivery');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forecastings', function (Blueprint $table) {
            $columns = ['outstanding_pre', 'stock_pre', 'delivery', 'outstanding'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('forecastings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
