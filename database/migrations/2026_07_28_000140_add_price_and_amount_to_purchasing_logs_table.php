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
        Schema::table('purchasing_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('purchasing_logs', 'price')) {
                $table->decimal('price', 15, 2)->default(0)->after('actual_received');
            }
            if (!Schema::hasColumn('purchasing_logs', 'amount')) {
                $table->decimal('amount', 15, 2)->default(0)->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchasing_logs', function (Blueprint $table) {
            if (Schema::hasColumn('purchasing_logs', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('purchasing_logs', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};
