<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure master_pos schema integrity
        if (Schema::hasTable('master_pos')) {
            Schema::table('master_pos', function (Blueprint $table) {
                if (!Schema::hasColumn('master_pos', 'category_id')) {
                    $table->unsignedBigInteger('category_id')->nullable()->index();
                }
                if (!Schema::hasColumn('master_pos', 'factory_code')) {
                    $table->string('factory_code', 50)->default('KIP 1')->index();
                }
                if (!Schema::hasColumn('master_pos', 'delivery_category_code')) {
                    $table->string('delivery_category_code', 20)->default('LOC');
                }
                if (!Schema::hasColumn('master_pos', 'price')) {
                    $table->decimal('price', 15, 4)->default(0);
                }
                if (!Schema::hasColumn('master_pos', 'currency')) {
                    $table->string('currency', 10)->default('USD');
                }
                if (!Schema::hasColumn('master_pos', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable();
                }
                if (!Schema::hasColumn('master_pos', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable();
                }
            });
        }

        // 2. Ensure purchasing_logs (Incoming) schema integrity
        if (Schema::hasTable('purchasing_logs')) {
            Schema::table('purchasing_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('purchasing_logs', 'purchasing_category_id')) {
                    $table->unsignedBigInteger('purchasing_category_id')->nullable()->index();
                }
                if (!Schema::hasColumn('purchasing_logs', 'factory_code')) {
                    $table->string('factory_code', 50)->default('KIP 1')->index();
                }
                if (!Schema::hasColumn('purchasing_logs', 'delivery_category_code')) {
                    $table->string('delivery_category_code', 20)->default('LOC');
                }
                if (!Schema::hasColumn('purchasing_logs', 'price')) {
                    $table->decimal('price', 15, 4)->default(0);
                }
                if (!Schema::hasColumn('purchasing_logs', 'currency')) {
                    $table->string('currency', 10)->default('USD');
                }
                if (!Schema::hasColumn('purchasing_logs', 'amount')) {
                    $table->decimal('amount', 18, 4)->default(0);
                }
                if (!Schema::hasColumn('purchasing_logs', 'production_qty')) {
                    $table->integer('production_qty')->default(0);
                }
                if (!Schema::hasColumn('purchasing_logs', 'pending_order')) {
                    $table->integer('pending_order')->default(0);
                }
                if (!Schema::hasColumn('purchasing_logs', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // Safe no-op to preserve operational data
    }
};
