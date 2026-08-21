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
            $table->integer('plan_stock')->default(0)->after('description'); // July Plan Stock
            $table->integer('m1_po')->default(0)->after('plan_stock');       // Aug PO
            $table->integer('m1_prod')->default(0)->after('m1_po');          // Aug PROD
            $table->integer('m2_po')->default(0)->after('m1_prod');          // Sep PO
            $table->integer('m2_prod')->default(0)->after('m2_po');          // Sep PROD
            $table->integer('m3_po')->default(0)->after('m2_prod');          // Oct PO
            $table->integer('m3_prod')->default(0)->after('m3_po');          // Oct PROD
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchasing_outstandings', function (Blueprint $table) {
            $table->dropColumn([
                'plan_stock',
                'm1_po',
                'm1_prod',
                'm2_po',
                'm2_prod',
                'm3_po',
                'm3_prod',
            ]);
        });
    }
};
