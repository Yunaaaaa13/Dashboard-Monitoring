<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ComparisonMaster extends Model
{
    use HasFactory;

    protected $table = 'purchasing_comparison_master';

    protected $fillable = [
        'part_number',
        'description',
        'periode',
        'outstanding_qty',
        'actual_po',
        'actual_production',
        'forecast_actual',
        'selisih',
        'coverage',
        'status',
        'status_badge',
        'has_outstanding',
        'has_forecast',
        'synced_at',
    ];

    protected $casts = [
        'has_outstanding' => 'boolean',
        'has_forecast'    => 'boolean',
        'synced_at'       => 'datetime',
        'coverage'        => 'float',
    ];

    /**
     * Sinkronisasi satu baris komparasi berdasarkan Part Number + Periode.
     * Dipanggil otomatis oleh observer Outstanding dan Actual.
     */
    public static function sync(string $partNumber, string $periode): void
    {
        if (!Schema::hasTable('purchasing_comparison_master')) {
            return;
        }

        $partNumber = strtoupper(trim($partNumber));

        // Ambil data dari master PurchasingOutstanding (untuk PO total & deskripsi)
        $masterPo = \App\Models\PurchasingOutstanding::where('part_number', $partNumber)->first();
        $desc     = $masterPo?->description ?? '-';

        // Ambil data dari ForecastActual (Realisasi/Sudah Diterima)
        $faRecord = \App\Models\ForecastActual::where('part_number', $partNumber)
            ->where('periode', $periode)->first();

        // Ambil data Outstanding (target per periode)
        $outstandingRecord = \App\Models\Outstanding::where('part_number', $partNumber)
            ->where(function ($q) use ($periode) {
                $q->where('periode', $periode)->orWhere('period_month', $periode);
            })->first();

        // Ambil data Actual (realisasi per periode)
        $actualRecord = \App\Models\Actual::where('part_number', $partNumber)
            ->where(function ($q) use ($periode) {
                $q->where('periode', $periode)->orWhere('period_month', $periode);
            })->first();

        if (!$outstandingRecord && !$actualRecord && !$faRecord) {
            static::where('part_number', $partNumber)->where('periode', $periode)->delete();
            return;
        }

        if ($desc === '-' && $faRecord?->description) {
            $desc = $faRecord->description;
        }

        $po               = (int) ($faRecord?->po ?? $masterPo?->order_qty ?? $outstandingRecord?->outstanding_qty ?? 0);
        $outstandingQty   = (int) ($outstandingRecord?->outstanding_qty ?? $po);
        $actualPo         = (int) max($actualRecord?->actual_po ?? 0, $actualRecord?->actual_qty ?? 0);
        $actualProduction = (int) ($actualRecord?->actual_production ?? 0);

        // Ambil Forecast Actual (Sudah Diterima / Realisasi Penerimaan PO)
        $forecastActual = 0;
        if ($faRecord && $faRecord->forecast_actual !== null && $faRecord->forecast_actual > 0) {
            $forecastActual = (int) $faRecord->forecast_actual;
        } elseif ($actualRecord && (($actualRecord->actual_po !== null && $actualRecord->actual_po > 0) || ($actualRecord->actual_qty !== null && $actualRecord->actual_qty > 0))) {
            $forecastActual = (int) max($actualRecord->actual_po ?? 0, $actualRecord->actual_qty ?? 0);
        } elseif ($po > 0 && $outstandingQty < $po) {
            $forecastActual = max(0, $po - $outstandingQty);
        }

        $hasOutstanding = (bool) ($outstandingRecord || $masterPo);
        $hasForecasting = (bool) ($faRecord || $actualRecord || $forecastActual > 0);

        // Kalkulasi Selisih & Coverage
        $selisih  = $forecastActual - $outstandingQty;
        $coverage = null;
        if ($outstandingQty > 0) {
            $coverage = round(($forecastActual / $outstandingQty) * 100, 2);
        } elseif ($outstandingQty === 0 && $po > 0) {
            $coverage = round(($forecastActual / $po) * 100, 2);
        } elseif ($outstandingQty === 0 && $forecastActual > 0) {
            $coverage = 100.0;
        }

        // Tentukan status berdasarkan coverage
        if (!$hasOutstanding && !$hasForecasting && $coverage === null) {
            $status      = 'Menunggu Data';
            $statusBadge = 'bg-secondary text-white';
        } elseif ($coverage === null) {
            $status      = 'Menunggu Data';
            $statusBadge = 'bg-secondary text-white';
        } elseif ($coverage > 100) {
            $status      = 'Material Aman';
            $statusBadge = 'bg-success text-white';
        } elseif ($coverage >= 90) {
            $status      = 'Perlu Monitoring';
            $statusBadge = 'bg-warning text-dark';
        } else {
            $status      = 'Kurang Material';
            $statusBadge = 'bg-danger text-white';
        }

        if (!$outstandingRecord && !$actualRecord && !$faRecord && !$masterPo) {
            static::where('part_number', $partNumber)->where('periode', $periode)->delete();
            return;
        }

        static::updateOrCreate(
            ['part_number' => $partNumber, 'periode' => $periode],
            [
                'description'      => $desc,
                'outstanding_qty'  => $outstandingQty,
                'actual_po'        => $actualPo,
                'actual_production' => $actualProduction,
                'forecast_actual'  => $forecastActual,
                'selisih'          => $selisih,
                'coverage'         => $coverage,
                'status'           => $status,
                'status_badge'     => $statusBadge,
                'has_outstanding'  => $hasOutstanding,
                'has_forecast'     => $hasForecasting,
                'synced_at'        => now(),
            ]
        );
    }

    /**
     * Hapus baris komparasi jika seluruh sumber data sudah tidak ada.
     */
    public static function syncDelete(string $partNumber, string $periode): void
    {
        if (!Schema::hasTable('purchasing_comparison_master')) {
            return;
        }

        $partNumber = strtoupper(trim($partNumber));

        $hasOut = \App\Models\Outstanding::where('part_number', $partNumber)
            ->where(function ($q) use ($periode) {
                $q->where('periode', $periode)->orWhere('period_month', $periode);
            })->exists();

        $hasAct = \App\Models\Actual::where('part_number', $partNumber)
            ->where(function ($q) use ($periode) {
                $q->where('periode', $periode)->orWhere('period_month', $periode);
            })->exists();

        $hasFa = \App\Models\ForecastActual::where('part_number', $partNumber)
            ->where('periode', $periode)->exists();

        $masterPo = \App\Models\PurchasingOutstanding::where('part_number', $partNumber)->first();

        if (!$hasOut && !$hasAct && !$hasFa) {
            static::where('part_number', $partNumber)->where('periode', $periode)->delete();
            if (!$masterPo) {
                static::where('part_number', $partNumber)->delete();
            }
        } else {
            static::sync($partNumber, $periode);
        }
    }

    /**
     * Sync seluruh data historis (dipakai saat migrasi atau resync).
     */
    public static function syncAll(): void
    {
        if (!Schema::hasTable('purchasing_comparison_master')) {
            return;
        }

        $outstandingPairs = \App\Models\Outstanding::select('part_number', 'periode')->get()
            ->map(fn($r) => $r->part_number . '|' . $r->periode);

        $actualPairs = \App\Models\Actual::select('part_number', 'periode')->get()
            ->map(fn($r) => $r->part_number . '|' . $r->periode);

        $faPairs = \App\Models\ForecastActual::select('part_number', 'periode')->get()
            ->map(fn($r) => $r->part_number . '|' . $r->periode);

        $allPairs = $outstandingPairs->merge($actualPairs)->merge($faPairs)->unique()->values();

        foreach ($allPairs as $pair) {
            [$partNumber, $periode] = explode('|', $pair, 2);
            if ($partNumber && $periode) {
                static::sync($partNumber, $periode);
            }
        }
    }
}
