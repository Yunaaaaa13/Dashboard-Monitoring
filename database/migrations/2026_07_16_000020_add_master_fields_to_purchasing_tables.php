<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ─── forecastings: tambah forecast_qty & pastikan description ada ───────
        if (Schema::hasTable('forecastings')) {
            Schema::table('forecastings', function (Blueprint $table) {
                if (!Schema::hasColumn('forecastings', 'forecast_qty')) {
                    $table->integer('forecast_qty')->default(0)->after('description');
                }
                // description sudah ada dari migrasi sebelumnya, skip jika ada
                if (!Schema::hasColumn('forecastings', 'description')) {
                    $table->string('description')->nullable()->after('part_number');
                }
            });

            // Isi forecast_qty dari stock_qty (data lama) jika masih 0
            DB::statement("UPDATE forecastings SET forecast_qty = stock_qty WHERE forecast_qty = 0 AND stock_qty > 0");
            DB::statement("UPDATE forecastings SET forecast_qty = po_qty   WHERE forecast_qty = 0 AND po_qty > 0");
        }

        // ─── actuals: tambah actual_qty & description ─────────────────────────
        if (Schema::hasTable('actuals')) {
            Schema::table('actuals', function (Blueprint $table) {
                if (!Schema::hasColumn('actuals', 'actual_qty')) {
                    $table->integer('actual_qty')->default(0)->after('periode');
                }
                if (!Schema::hasColumn('actuals', 'description')) {
                    $table->string('description')->nullable()->after('part_number');
                }
            });

            // Isi actual_qty dari actual_stock (data lama) jika masih 0
            DB::statement("UPDATE actuals SET actual_qty = actual_stock WHERE actual_qty = 0 AND actual_stock > 0");
        }

        // ─── outstandings: tambah description & po ────────────────────────────
        if (Schema::hasTable('outstandings')) {
            Schema::table('outstandings', function (Blueprint $table) {
                if (!Schema::hasColumn('outstandings', 'description')) {
                    $table->string('description')->nullable()->after('part_number');
                }
                if (!Schema::hasColumn('outstandings', 'po')) {
                    $table->string('po')->nullable()->after('description');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('forecastings')) {
            Schema::table('forecastings', function (Blueprint $table) {
                if (Schema::hasColumn('forecastings', 'forecast_qty')) {
                    $table->dropColumn('forecast_qty');
                }
            });
        }
        if (Schema::hasTable('actuals')) {
            Schema::table('actuals', function (Blueprint $table) {
                if (Schema::hasColumn('actuals', 'actual_qty')) {
                    $table->dropColumn('actual_qty');
                }
                if (Schema::hasColumn('actuals', 'description')) {
                    $table->dropColumn('description');
                }
            });
        }
        if (Schema::hasTable('outstandings')) {
            Schema::table('outstandings', function (Blueprint $table) {
                if (Schema::hasColumn('outstandings', 'description')) {
                    $table->dropColumn('description');
                }
                if (Schema::hasColumn('outstandings', 'po')) {
                    $table->dropColumn('po');
                }
            });
        }
    }
};
