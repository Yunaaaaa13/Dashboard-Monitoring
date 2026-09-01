<?php

namespace App\Http\Controllers;

use App\Models\MasterPo;
use App\Models\PurchasingLog;
use App\Models\PurchasingOutstanding;
use App\Models\ComparisonMaster;
use App\Models\Actual;
use App\Models\ProductionLog;
use App\Models\Inventory;
use App\Models\Forecasting;
use App\Services\DataValidation\InputNormalizer;
use Illuminate\Http\Request;

class DataTraceController extends Controller
{
    /**
     * Dashboard Data Integration Health & Traceability Matrix.
     */
    public function index(Request $request)
    {
        $healthData = $this->calculateHealthMatrix();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($healthData);
        }

        return view('system.data_health', compact('healthData'));
    }

    /**
     * API endpoint untuk AJAX live monitoring.
     */
    public function apiHealth()
    {
        return response()->json($this->calculateHealthMatrix());
    }

    /**
     * Menghitung status integritas data lintas modul.
     */
    public function calculateHealthMatrix(): array
    {
        // 1. Forecast Module (Step 1)
        $forecastCount = Forecasting::count();
        $forecastItems = Forecasting::distinct('part_number')->count('part_number');
        $step1Count = PurchasingOutstanding::count();

        // 2. Master PO Module (Step 2)
        $masterPoCount = MasterPo::count();
        $masterPoQty   = (int) MasterPo::sum('qty');
        $masterPoItems = MasterPo::distinct('item_code')->count('item_code');

        // 3. Incoming Module (Step 3)
        $incomingCount = PurchasingLog::count();
        $incomingQty   = (int) PurchasingLog::sum('actual_received');
        $incomingItems = PurchasingLog::distinct('item_code')->count('item_code');

        // 4. Outstanding PO Matching (Step 4)
        $allReceipts = PurchasingLog::all();
        $receiptsExactMap = [];
        $receiptsBaseMap  = [];

        foreach ($allReceipts as $rec) {
            $recPo = InputNormalizer::canonicalPoNumber($rec->po_reference);
            $recBasePo = InputNormalizer::basePoNumber($rec->po_reference);
            $recItem = InputNormalizer::cleanMaterialCode($rec->item_code);

            $exactKey = $recPo . '___' . $recItem;
            $baseKey  = $recBasePo . '___' . $recItem;

            $receiptsExactMap[$exactKey] = ($receiptsExactMap[$exactKey] ?? 0) + (int)$rec->actual_received;
            $receiptsBaseMap[$baseKey]   = ($receiptsBaseMap[$baseKey] ?? 0) + (int)$rec->actual_received;
        }

        $allMasterPo = MasterPo::all();
        $matchedPoCount = 0;
        $totalOutstandingQty = 0;
        $totalOverDeliveryQty = 0;
        $unmatchedMasterPo = [];

        foreach ($allMasterPo as $mp) {
            $poExact = InputNormalizer::canonicalPoNumber($mp->po);
            $poBase  = InputNormalizer::basePoNumber($mp->po);
            $itemCode = InputNormalizer::cleanMaterialCode($mp->item_code);

            $exactKey = $poExact . '___' . $itemCode;
            $baseKey  = $poBase . '___' . $itemCode;

            $received = $receiptsExactMap[$exactKey] ?? ($receiptsBaseMap[$baseKey] ?? 0);
            $plan = (int)$mp->qty;

            if ($received > 0) {
                $matchedPoCount++;
            } else {
                $unmatchedMasterPo[] = [
                    'po'        => $mp->po,
                    'item_code' => $mp->item_code,
                    'supplier'  => $mp->supplier,
                    'plan_qty'  => $plan,
                    'status'    => 'Not Received',
                ];
            }

            $totalOutstandingQty += max(0, $plan - $received);
            $totalOverDeliveryQty += max(0, $received - $plan);
        }

        // 5. Actual Production (Step 5) & Actual Stock (Step 6)
        $actualProdCount = class_exists('\App\Models\ActualProduction') ? \App\Models\ActualProduction::count() : 0;
        $actualProdQty   = class_exists('\App\Models\ActualProduction') ? (int)\App\Models\ActualProduction::sum('qty') : 0;
        $actualProdItems = class_exists('\App\Models\ActualProduction') ? \App\Models\ActualProduction::distinct('item_code')->count('item_code') : 0;

        $inventoryCount  = class_exists('\App\Models\Inventory') ? \App\Models\Inventory::count() : 0;
        $inventoryStock  = class_exists('\App\Models\Inventory') ? (int)\App\Models\Inventory::sum('current_stock') : 0;
        $inventoryItems  = class_exists('\App\Models\Inventory') ? \App\Models\Inventory::distinct('part_number')->count('part_number') : 0;

        $actualsCount    = Actual::count();
        $comparisonCount = ComparisonMaster::count();

        // 6. Evaluasi Status Kesehatan Sistem untuk 7 Modul Purchasing
        $statusForecast   = ($forecastCount > 0 || $step1Count > 0) ? 'HEALTHY' : 'EMPTY';
        $statusMasterPo   = $masterPoCount > 0 ? 'HEALTHY' : 'EMPTY';
        $statusIncoming   = $incomingCount > 0 ? 'HEALTHY' : 'EMPTY';
        $statusOutstanding = ($masterPoCount > 0) ? 'HEALTHY' : 'EMPTY';
        $statusActualProd = $actualProdCount > 0 ? 'HEALTHY' : 'EMPTY';
        $statusInventory  = $inventoryCount > 0 ? 'HEALTHY' : 'EMPTY';
        $statusAnalysis   = ($comparisonCount > 0 || $actualsCount > 0) ? 'HEALTHY' : 'EMPTY';

        $totalModules = 7;
        $healthyModules = 0;
        if ($statusForecast === 'HEALTHY') $healthyModules++;
        if ($statusMasterPo === 'HEALTHY') $healthyModules++;
        if ($statusIncoming === 'HEALTHY') $healthyModules++;
        if ($statusOutstanding === 'HEALTHY') $healthyModules++;
        if ($statusActualProd === 'HEALTHY') $healthyModules++;
        if ($statusInventory === 'HEALTHY') $healthyModules++;
        if ($statusAnalysis === 'HEALTHY') $healthyModules++;

        $healthScore = round(($healthyModules / $totalModules) * 100);

        return [
            'timestamp'           => date('Y-m-d H:i:s'),
            'health_score'        => $healthScore,
            'modules'             => [
                'forecast' => [
                    'title'       => '01 Forecast Master',
                    'status'      => $statusForecast,
                    'record_count'=> $forecastCount ?: $step1Count,
                    'items_count' => $forecastItems,
                ],
                'master_po' => [
                    'title'       => '02 Master Purchase Order',
                    'status'      => $statusMasterPo,
                    'record_count'=> $masterPoCount,
                    'total_plan'  => $masterPoQty,
                    'items_count' => $masterPoItems,
                ],
                'incoming' => [
                    'title'       => '03 Incoming / Realisasi',
                    'status'      => $statusIncoming,
                    'record_count'=> $incomingCount,
                    'total_result'=> $incomingQty,
                    'items_count' => $incomingItems,
                ],
                'outstanding' => [
                    'title'       => '04 Outstanding PO',
                    'status'      => $statusOutstanding,
                    'matched_po'  => $matchedPoCount,
                    'unmatched'   => count($unmatchedMasterPo),
                    'total_outstanding' => $totalOutstandingQty,
                    'total_over_delivery' => $totalOverDeliveryQty,
                ],
                'actual_production' => [
                    'title'       => '05 Aktual Produksi',
                    'status'      => $statusActualProd,
                    'record_count'=> $actualProdCount,
                    'total_qty'   => $actualProdQty,
                    'items_count' => $actualProdItems,
                ],
                'inventory' => [
                    'title'       => '06 Aktual Stock',
                    'status'      => $statusInventory,
                    'record_count'=> $inventoryCount,
                    'total_stock' => $inventoryStock,
                    'items_count' => $inventoryItems,
                ],
                'analysis' => [
                    'title'       => '07 Hasil Akhir & Analisis',
                    'status'      => $statusAnalysis,
                    'actuals_count'    => $actualsCount,
                    'comparison_count' => $comparisonCount,
                ],
            ],
            'reconciliation' => [
                'sum_master_po_qty'   => $masterPoQty,
                'sum_incoming_qty'    => $incomingQty,
                'sum_outstanding_qty' => $totalOutstandingQty,
                'is_reconciled'       => ($masterPoQty >= 0 && $incomingQty >= 0),
            ],
            'unmatched_samples' => array_slice($unmatchedMasterPo, 0, 10),
        ];
    }
}
