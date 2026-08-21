<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('actuals')) {
            Schema::create('actuals', function (Blueprint $table) {
                $table->id();
                $table->string('part_number');
                $table->string('periode')->nullable();
                $table->integer('actual_po')->default(0);
                $table->integer('actual_production')->default(0);
                $table->integer('actual_stock')->default(0);
                $table->string('period_month')->nullable();
                $table->timestamps();

                $table->unique(['part_number', 'periode']);
            });

            // Migrate any existing actual data from forecastings table
            if (Schema::hasTable('forecastings')) {
                $forecasts = DB::table('forecastings')
                    ->where(function ($q) {
                        $q->where('actual', '>', 0)->orWhere('actual_qty', '>', 0);
                    })
                    ->get();

                foreach ($forecasts as $f) {
                    $periode = !empty($f->periode) ? $f->periode : (!empty($f->period_month) ? $f->period_month : null);
                    if ($periode && !empty($f->part_number)) {
                        $actualVal = (int) ($f->actual ?? $f->actual_qty ?? 0);
                        DB::table('actuals')->updateOrInsert(
                            ['part_number' => $f->part_number, 'periode' => $periode],
                            [
                                'actual_po'         => 0,
                                'actual_production' => 0,
                                'actual_stock'      => $actualVal,
                                'period_month'      => $periode,
                                'created_at'        => $f->created_at ?? now(),
                                'updated_at'        => $f->updated_at ?? now(),
                            ]
                        );
                    }
                }
            }
        } else {
            Schema::table('actuals', function (Blueprint $table) {
                if (!Schema::hasColumn('actuals', 'periode')) {
                    $table->string('periode')->nullable()->after('part_number');
                }
                if (!Schema::hasColumn('actuals', 'period_month')) {
                    $table->string('period_month')->nullable()->after('actual_stock');
                }
                if (!Schema::hasColumn('actuals', 'actual_po')) {
                    $table->integer('actual_po')->default(0)->after('periode');
                }
                if (!Schema::hasColumn('actuals', 'actual_production')) {
                    $table->integer('actual_production')->default(0)->after('actual_po');
                }
                if (!Schema::hasColumn('actuals', 'actual_stock')) {
                    $table->integer('actual_stock')->default(0)->after('actual_production');
                }
            });
            DB::statement("UPDATE actuals SET periode = period_month WHERE periode IS NULL OR periode = ''");
            DB::statement("UPDATE actuals SET period_month = periode WHERE period_month IS NULL OR period_month = ''");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('actuals');
    }
};
