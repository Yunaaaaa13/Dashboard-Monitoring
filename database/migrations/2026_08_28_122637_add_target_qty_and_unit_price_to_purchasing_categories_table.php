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
        Schema::table('purchasing_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('purchasing_categories', 'target_qty')) {
                $table->integer('target_qty')->nullable()->after('monthly_target_units');
            }
            if (!Schema::hasColumn('purchasing_categories', 'unit_price')) {
                $table->decimal('unit_price', 15, 4)->nullable()->after('target_qty');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchasing_categories', function (Blueprint $table) {
            if (Schema::hasColumn('purchasing_categories', 'unit_price')) {
                $table->dropColumn('unit_price');
            }
            if (Schema::hasColumn('purchasing_categories', 'target_qty')) {
                $table->dropColumn('target_qty');
            }
        });
    }
};
