<?php

namespace App\Http\Controllers;

use App\Models\Forecasting;
use App\Models\Outstanding;
use Illuminate\Http\Request;

class ForecastComparisonController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m'));
        $forecasts = Forecasting::where('periode', $period)->orWhere('period_month', $period)->orderBy('part_number')->get()->keyBy('part_number');
        $outstandings = Outstanding::where('periode', $period)->orWhere('period_month', $period)->orderBy('part_number')->get()->keyBy('part_number');

        $partNumbers = $forecasts->keys()->merge($outstandings->keys())->unique()->sort()->values();
        $comparisons = $partNumbers->map(function ($partNumber) use ($forecasts, $outstandings, $period) {
            $forecast = $forecasts->get($partNumber);
            $outstanding = $outstandings->get($partNumber);
            $stock = (int) ($forecast?->stock ?? $forecast?->stock_qty ?? 0);
            $actual = (int) ($forecast?->actual ?? $forecast?->actual_qty ?? 0);
            $po = (int) ($forecast?->po ?? $forecast?->po_qty ?? 0);
            $production = (int) ($forecast?->production ?? $forecast?->production_qty ?? 0);
            $outstandingQty = (int) ($outstanding?->outstanding_qty ?? 0);
            
            $coverage = $outstandingQty > 0 ? round(($stock / $outstandingQty) * 100, 1) : null;
            $stockDifference = $stock - $outstandingQty;
            $actualDifference = $actual - $outstandingQty;

            if ($outstandingQty === 0) {
                $status = 'Belum Ada Outstanding';
            } elseif ($coverage > 110) {
                $status = 'Aman';
            } elseif ($coverage >= 90) {
                $status = 'Warning';
            } else {
                $status = 'Kurang';
            }

            return (object) [
                'part_number' => $partNumber,
                'description' => $forecast?->description ?? '-',
                'period' => $period,
                'periode' => $period,
                'po' => $po,
                'po_qty' => $po,
                'production' => $production,
                'production_qty' => $production,
                'stock' => $stock,
                'stock_qty' => $stock,
                'actual' => $actual,
                'actual_qty' => $actual,
                'outstanding_qty' => $outstandingQty,
                'coverage' => $coverage,
                'stock_difference' => $stockDifference,
                'actual_difference' => $actualDifference,
                'status' => $status,
                'has_forecast' => (bool) $forecast,
                'has_outstanding' => (bool) $outstanding,
            ];
        });

        $covered = $comparisons->whereNotNull('coverage');
        $availablePeriods = Forecasting::pluck('periode')
            ->merge(Forecasting::pluck('period_month'))
            ->merge(Outstanding::pluck('periode'))
            ->merge(Outstanding::pluck('period_month'))
            ->unique()->filter()->sortDesc()->values();
        if (!$availablePeriods->contains($period)) $availablePeriods->prepend($period);

        return view('comparison.index', [
            'period' => $period,
            'availablePeriods' => $availablePeriods,
            'comparisons' => $comparisons,
            'forecastCount' => $forecasts->count(),
            'outstandingCount' => $outstandings->count(),
            'averageCoverage' => $covered->isNotEmpty() ? round($covered->avg('coverage'), 1) : null,
            'safeCount' => $comparisons->where('status', 'Aman')->count(),
            'warningCount' => $comparisons->where('status', 'Warning')->count(),
            'shortageCount' => $comparisons->where('status', 'Kurang')->count(),
        ]);
    }

    public function storeForecast(Request $request)
    {
        return app(ForecastController::class)->store($request);
    }

    public function storeOutstanding(Request $request)
    {
        return app(OutstandingController::class)->store($request);
    }
}
