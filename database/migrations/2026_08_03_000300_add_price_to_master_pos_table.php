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
        if (Schema::hasTable('master_pos') && !Schema::hasColumn('master_pos', 'price')) {
            Schema::table('master_pos', function (Blueprint $table) {
                $table->decimal('price', 15, 2)->default(0.00)->after('qty');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('master_pos') && Schema::hasColumn('master_pos', 'price')) {
            Schema::table('master_pos', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }
    }
};
