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
        if (Schema::hasTable('master_pos') && !Schema::hasColumn('master_pos', 'category_id')) {
            Schema::table('master_pos', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->after('delivery_category_code')->constrained('purchasing_categories')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('master_pos') && Schema::hasColumn('master_pos', 'category_id')) {
            Schema::table('master_pos', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }
};
