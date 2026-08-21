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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'can_view_user_monitoring')) {
                    $table->boolean('can_view_user_monitoring')->default(false)->after('permissions');
                }
                if (!Schema::hasColumn('users', 'admin_note')) {
                    $table->text('admin_note')->nullable()->after('can_view_user_monitoring');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'can_view_user_monitoring')) {
                    $table->dropColumn('can_view_user_monitoring');
                }
                if (Schema::hasColumn('users', 'admin_note')) {
                    $table->dropColumn('admin_note');
                }
            });
        }
    }
};
