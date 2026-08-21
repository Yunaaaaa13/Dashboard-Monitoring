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
        if (Schema::hasTable('purchasing_outstandings')) {
            Schema::table('purchasing_outstandings', function (Blueprint $table) {
                if (!Schema::hasColumn('purchasing_outstandings', 'm0_inventory')) {
                    $table->integer('m0_inventory')->nullable()->default(0)->after('plan_stock');
                }

                for ($i = 1; $i <= 36; $i++) {
                    $colName = "m{$i}_inventory";
                    if (!Schema::hasColumn('purchasing_outstandings', $colName)) {
                        $afterCol = "m{$i}_stock";
                        if (!Schema::hasColumn('purchasing_outstandings', $afterCol)) {
                            $afterCol = 'm0_inventory';
                        }
                        $table->integer($colName)->nullable()->default(0)->after($afterCol);
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchasing_outstandings')) {
            Schema::table('purchasing_outstandings', function (Blueprint $table) {
                if (Schema::hasColumn('purchasing_outstandings', 'm0_inventory')) {
                    $table->dropColumn('m0_inventory');
                }
                for ($i = 1; $i <= 36; $i++) {
                    $colName = "m{$i}_inventory";
                    if (Schema::hasColumn('purchasing_outstandings', $colName)) {
                        $table->dropColumn($colName);
                    }
                }
            });
        }
    }
};
