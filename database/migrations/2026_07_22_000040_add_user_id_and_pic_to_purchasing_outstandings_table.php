<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchasing_outstandings')) {
            Schema::table('purchasing_outstandings', function (Blueprint $table) {
                if (!Schema::hasColumn('purchasing_outstandings', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('category_id');
                }
                if (!Schema::hasColumn('purchasing_outstandings', 'pic_buyer')) {
                    $table->string('pic_buyer')->nullable()->after('supplier_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchasing_outstandings')) {
            Schema::table('purchasing_outstandings', function (Blueprint $table) {
                if (Schema::hasColumn('purchasing_outstandings', 'user_id')) {
                    $table->dropColumn('user_id');
                }
                if (Schema::hasColumn('purchasing_outstandings', 'pic_buyer')) {
                    $table->dropColumn('pic_buyer');
                }
            });
        }
    }
};
