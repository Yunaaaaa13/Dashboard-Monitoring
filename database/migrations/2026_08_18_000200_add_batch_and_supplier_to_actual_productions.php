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
        Schema::table('actual_productions', function (Blueprint $table) {
            if (!Schema::hasColumn('actual_productions', 'supplier_code')) {
                $table->string('supplier_code', 50)->nullable()->after('item_code');
            }
            if (!Schema::hasColumn('actual_productions', 'supplier_name')) {
                $table->string('supplier_name', 255)->nullable()->after('supplier_code');
            }
            if (!Schema::hasColumn('actual_productions', 'description')) {
                $table->string('description', 255)->nullable()->after('supplier_name');
            }
            if (!Schema::hasColumn('actual_productions', 'import_batch_id')) {
                $table->string('import_batch_id', 100)->nullable()->after('delivery_category_code');
            }
            if (!Schema::hasColumn('actual_productions', 'excel_row_number')) {
                $table->integer('excel_row_number')->nullable()->after('import_batch_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actual_productions', function (Blueprint $table) {
            $table->dropColumn([
                'supplier_code',
                'supplier_name',
                'description',
                'import_batch_id',
                'excel_row_number',
            ]);
        });
    }
};
