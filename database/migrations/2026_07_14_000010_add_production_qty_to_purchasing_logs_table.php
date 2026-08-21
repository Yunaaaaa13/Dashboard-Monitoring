<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchasing_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('purchasing_logs', 'production_qty')) {
                $table->integer('production_qty')->default(0)->after('actual_received');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchasing_logs', function (Blueprint $table) {
            if (Schema::hasColumn('purchasing_logs', 'production_qty')) {
                $table->dropColumn('production_qty');
            }
        });
    }
};
