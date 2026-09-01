<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SafeSchemaModelTrait;

class Outstanding extends Model
{
    use HasFactory, SafeSchemaModelTrait;

    protected $table = 'outstandings';

    protected $fillable = [
        'part_number', 'factory_code', 'description', 'po', 'periode', 'outstanding_qty', 'period_month',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->periode && !$model->period_month) {
                $model->period_month = $model->periode;
            }
            if ($model->period_month && !$model->periode) {
                $model->periode = $model->period_month;
            }
        });

        static::saved(function ($model) {
            if (\Illuminate\Support\Facades\Schema::hasTable('outstanding_records')) {
                \Illuminate\Support\Facades\DB::table('outstanding_records')->updateOrInsert(
                    [
                        'part_number'  => $model->part_number,
                        'period_month' => $model->period_month ?? $model->periode,
                    ],
                    [
                        'periode'         => $model->periode ?? $model->period_month,
                        'outstanding_qty' => $model->outstanding_qty,
                        'created_at'      => $model->created_at ?? now(),
                        'updated_at'      => $model->updated_at ?? now(),
                    ]
                );
            }

            // Sync ke Forecast Actual = PO - Outstanding
            if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_forecast_actuals')) {
                $master = \App\Models\PurchasingOutstanding::where('part_number', $model->part_number)->first();
                $po = (int) ($master?->order_qty ?? 0);
                $outstanding = (int) $model->outstanding_qty;
                $forecastActual = $po - $outstanding;
                $periode = $model->periode ?? $model->period_month ?? date('Y-m');

                \App\Models\ForecastActual::updateOrCreate(
                    [
                        'part_number' => $model->part_number,
                        'periode'     => $periode,
                    ],
                    [
                        'description'     => $model->description ?? ($master?->description ?? '-'),
                        'po'              => $po,
                        'forecast_actual' => $forecastActual,
                    ]
                );

                // Auto-sync ke Master Komparasi
                \App\Models\ComparisonMaster::sync($model->part_number, $periode);
            }
        });

        static::deleted(function ($model) {
            $periode = $model->periode ?? $model->period_month ?? date('Y-m');
            if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_forecast_actuals')) {
                \App\Models\ForecastActual::where('part_number', $model->part_number)
                    ->where('periode', $periode)
                    ->delete();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_comparison_master')) {
                \App\Models\ComparisonMaster::syncDelete($model->part_number, $periode);
            }
        });
    }
}
