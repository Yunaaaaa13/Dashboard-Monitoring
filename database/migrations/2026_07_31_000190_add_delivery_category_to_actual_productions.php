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
        if (Schema::hasTable('actual_productions') && !Schema::hasColumn('actual_productions', 'delivery_category_code')) {
            Schema::table('actual_productions', function (Blueprint $table) {
                $table->string('delivery_category_code', 50)->nullable()->default('LOC')->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('actual_productions') && Schema::hasColumn('actual_productions', 'delivery_category_code')) {
            Schema::table('actual_productions', function (Blueprint $table) {
                $table->dropColumn('delivery_category_code');
            });
        }
    }
};
