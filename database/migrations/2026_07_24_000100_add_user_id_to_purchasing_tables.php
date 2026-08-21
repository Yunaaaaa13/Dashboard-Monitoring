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
        if (Schema::hasTable('forecastings')) {
            Schema::table('forecastings', function (Blueprint $table) {
                if (!Schema::hasColumn('forecastings', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('part_number');
                }
            });
        }

        if (Schema::hasTable('master_pos')) {
            Schema::table('master_pos', function (Blueprint $table) {
                if (!Schema::hasColumn('master_pos', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('created_by');
                }
            });
        }

        if (Schema::hasTable('actual_productions')) {
            Schema::table('actual_productions', function (Blueprint $table) {
                if (!Schema::hasColumn('actual_productions', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('item_code');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('forecastings')) {
            Schema::table('forecastings', function (Blueprint $table) {
                if (Schema::hasColumn('forecastings', 'user_id')) {
                    $table->dropColumn('user_id');
                }
            });
        }

        if (Schema::hasTable('master_pos')) {
            Schema::table('master_pos', function (Blueprint $table) {
                if (Schema::hasColumn('master_pos', 'user_id')) {
                    $table->dropColumn('user_id');
                }
            });
        }

        if (Schema::hasTable('actual_productions')) {
            Schema::table('actual_productions', function (Blueprint $table) {
                if (Schema::hasColumn('actual_productions', 'user_id')) {
                    $table->dropColumn('user_id');
                }
            });
        }
    }
};
