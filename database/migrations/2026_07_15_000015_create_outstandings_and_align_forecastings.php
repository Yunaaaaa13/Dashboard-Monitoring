<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('forecastings')) {
            Schema::table('forecastings', function (Blueprint $table) {
                if (!Schema::hasColumn('forecastings', 'periode')) {
                    $table->string('periode')->nullable()->after('description');
                }
                if (!Schema::hasColumn('forecastings', 'po')) {
                    $table->integer('po')->default(0)->after('periode');
                }
                if (!Schema::hasColumn('forecastings', 'production')) {
                    $table->integer('production')->default(0)->after('po');
                }
                if (!Schema::hasColumn('forecastings', 'stock')) {
                    $table->integer('stock')->default(0)->after('production');
                }
                if (!Schema::hasColumn('forecastings', 'actual')) {
                    $table->integer('actual')->default(0)->after('stock');
                }
                if (!Schema::hasColumn('forecastings', 'period_month')) {
                    $table->string('period_month')->nullable()->after('actual');
                }
                if (!Schema::hasColumn('forecastings', 'po_qty')) {
                    $table->integer('po_qty')->default(0)->after('period_month');
                }
                if (!Schema::hasColumn('forecastings', 'production_qty')) {
                    $table->integer('production_qty')->default(0)->after('po_qty');
                }
                if (!Schema::hasColumn('forecastings', 'stock_qty')) {
                    $table->integer('stock_qty')->default(0)->after('production_qty');
                }
                if (!Schema::hasColumn('forecastings', 'actual_qty')) {
                    $table->integer('actual_qty')->default(0)->after('stock_qty');
                }
            });

            // Copy existing values if periode is empty and period_month exists
            \Illuminate\Support\Facades\DB::statement("UPDATE forecastings SET periode = period_month WHERE periode IS NULL OR periode = ''");
            \Illuminate\Support\Facades\DB::statement("UPDATE forecastings SET period_month = periode WHERE period_month IS NULL OR period_month = ''");
            \Illuminate\Support\Facades\DB::statement("UPDATE forecastings SET po = po_qty WHERE po = 0 AND po_qty > 0");
            \Illuminate\Support\Facades\DB::statement("UPDATE forecastings SET po_qty = po WHERE po_qty = 0 AND po > 0");
            \Illuminate\Support\Facades\DB::statement("UPDATE forecastings SET production = production_qty WHERE production = 0 AND production_qty > 0");
            \Illuminate\Support\Facades\DB::statement("UPDATE forecastings SET production_qty = production WHERE production_qty = 0 AND production > 0");
            \Illuminate\Support\Facades\DB::statement("UPDATE forecastings SET stock = stock_qty WHERE stock = 0 AND stock_qty > 0");
            \Illuminate\Support\Facades\DB::statement("UPDATE forecastings SET stock_qty = stock WHERE stock_qty = 0 AND stock > 0");
            \Illuminate\Support\Facades\DB::statement("UPDATE forecastings SET actual = actual_qty WHERE actual = 0 AND actual_qty > 0");
            \Illuminate\Support\Facades\DB::statement("UPDATE forecastings SET actual_qty = actual WHERE actual_qty = 0 AND actual > 0");
        } else {
            Schema::create('forecastings', function (Blueprint $table) {
                $table->id();
                $table->string('part_number');
                $table->string('description')->nullable();
                $table->string('periode')->nullable();
                $table->integer('po')->default(0);
                $table->integer('production')->default(0);
                $table->integer('stock')->default(0);
                $table->integer('actual')->default(0);
                $table->string('period_month')->nullable();
                $table->integer('po_qty')->default(0);
                $table->integer('production_qty')->default(0);
                $table->integer('stock_qty')->default(0);
                $table->integer('actual_qty')->default(0);
                $table->timestamps();

                $table->unique(['part_number', 'periode']);
            });
        }

        if (!Schema::hasTable('outstandings')) {
            Schema::create('outstandings', function (Blueprint $table) {
                $table->id();
                $table->string('part_number');
                $table->string('periode')->nullable();
                $table->integer('outstanding_qty')->default(0);
                $table->string('period_month')->nullable();
                $table->timestamps();

                $table->unique(['part_number', 'periode']);
            });

            // If outstanding_records exists, migrate existing data to outstandings
            if (Schema::hasTable('outstanding_records')) {
                $records = \Illuminate\Support\Facades\DB::table('outstanding_records')->get();
                foreach ($records as $r) {
                    \Illuminate\Support\Facades\DB::table('outstandings')->updateOrInsert(
                        ['part_number' => $r->part_number, 'periode' => $r->period_month],
                        [
                            'outstanding_qty' => $r->outstanding_qty,
                            'period_month' => $r->period_month,
                            'created_at' => $r->created_at ?? now(),
                            'updated_at' => $r->updated_at ?? now(),
                        ]
                    );
                }
            }
        } else {
            Schema::table('outstandings', function (Blueprint $table) {
                if (!Schema::hasColumn('outstandings', 'periode')) {
                    $table->string('periode')->nullable()->after('part_number');
                }
                if (!Schema::hasColumn('outstandings', 'period_month')) {
                    $table->string('period_month')->nullable()->after('outstanding_qty');
                }
            });
            \Illuminate\Support\Facades\DB::statement("UPDATE outstandings SET periode = period_month WHERE periode IS NULL OR periode = ''");
            \Illuminate\Support\Facades\DB::statement("UPDATE outstandings SET period_month = periode WHERE period_month IS NULL OR period_month = ''");
        }

        // Also ensure outstanding_records has periode column if used by legacy checks
        if (Schema::hasTable('outstanding_records')) {
            if (!Schema::hasColumn('outstanding_records', 'periode')) {
                Schema::table('outstanding_records', function (Blueprint $table) {
                    $table->string('periode')->nullable()->after('part_number');
                });
            }
            \Illuminate\Support\Facades\DB::statement("UPDATE outstanding_records SET periode = period_month WHERE periode IS NULL OR periode = ''");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('outstandings');
    }
};
