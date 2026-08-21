<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actual extends Model
{
    use HasFactory;

    protected $table = 'actuals';

    protected $fillable = [
        'part_number',
        'factory_code',
        'description',
        'periode',
        'actual_qty',
        // Legacy fields (dipertahankan)
        'actual_po',
        'actual_production',
        'actual_stock',
        'period_month',
    ];

    /**
     * Accessor: actual_qty → fallback ke actual_stock (legacy) jika belum diisi.
     */
    public function getActualQtyAttribute($value)
    {
        if ($value !== null && $value > 0) {
            return $value;
        }
        return $this->attributes['actual_stock'] ?? 0;
    }

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
            // Keep legacy forecasting actual synced if needed for backward compatibility
            if ($model->part_number && $model->periode) {
                $forecast = Forecasting::where('part_number', $model->part_number)
                    ->where(function ($q) use ($model) {
                        $q->where('periode', $model->periode)
                          ->orWhere('period_month', $model->periode);
                    })->first();

                if ($forecast) {
                    $forecast->actual = $model->actual_stock ?? $model->actual_qty ?? 0;
                    $forecast->actual_qty = $model->actual_stock ?? $model->actual_qty ?? 0;
                    $forecast->saveQuietly();
                }

                // Auto-sync ke Master Komparasi
                \App\Models\ComparisonMaster::sync($model->part_number, $model->periode);
            }
        });

        static::deleted(function ($model) {
            $periode = $model->periode ?? $model->period_month ?? date('Y-m');
            \App\Models\ComparisonMaster::syncDelete($model->part_number, $periode);
        });
    }
}
