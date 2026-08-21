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
        if (Schema::hasTable('inventories') && !Schema::hasColumn('inventories', 'supplier_code')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->string('supplier_code')->nullable()->after('supplier_name')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inventories') && Schema::hasColumn('inventories', 'supplier_code')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropColumn('supplier_code');
            });
        }
    }
};
