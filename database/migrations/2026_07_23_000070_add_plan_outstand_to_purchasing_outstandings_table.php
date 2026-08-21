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
        Schema::table('purchasing_outstandings', function (Blueprint $table) {
            if (!Schema::hasColumn('purchasing_outstandings', 'plan_outstand')) {
                $table->integer('plan_outstand')->default(0)->after('plan_stock');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchasing_outstandings', function (Blueprint $table) {
            if (Schema::hasColumn('purchasing_outstandings', 'plan_outstand')) {
                $table->dropColumn('plan_outstand');
            }
        });
    }
};
