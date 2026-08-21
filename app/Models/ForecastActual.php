<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastActual extends Model
{
    use HasFactory;

    protected $table = 'purchasing_forecast_actuals';

    protected $fillable = [
        'part_number',
        'description',
        'periode',
        'po',
        'forecast_actual',
    ];

    protected static function booted()
    {
        static::saved(function ($model) {
            if ($model->part_number && $model->periode) {
                \App\Models\ComparisonMaster::sync($model->part_number, $model->periode);
            }
        });

        static::deleted(function ($model) {
            if ($model->part_number && $model->periode) {
                \App\Models\ComparisonMaster::syncDelete($model->part_number, $model->periode);
            }
        });
    }
}
