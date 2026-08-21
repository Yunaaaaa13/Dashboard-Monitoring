<?php

namespace App\Services\Matching;

use App\Models\MasterPo;
use App\Models\PurchasingLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReconciliationService
{
    protected PoIncomingMatcher $matcher;

    public function __construct(PoIncomingMatcher $matcher)
    {
        $this->matcher = $matcher;
    }

    /**
     * Run full reconciliation for all data or filtered by period.
     * Computes dynamic horizon, matches PO vs Incoming, and stores results.
     *
     * @param string|null $periodMin
     * @param string|null $periodMax
     * @return ReconciliationReport
     */
    public function reconcile(?string $periodMin = null, ?string $periodMax = null): ReconciliationReport
    {
        $horizon = $this->detectHorizon();

        $min = $periodMin ?? $horizon['min'] ?? date('Y-m');
        $max = $periodMax ?? $horizon['max'] ?? date('Y-m');

        $report = new ReconciliationReport();
        $report->periodMin = $min;
        $report->periodMax = $max;

        // Generate list of months
        $months = [];
        $current = Carbon::parse($min . '-01');
        $endDate = Carbon::parse($max . '-01');
        while ($current <= $endDate) {
            $months[] = $current->format('Y-m');
            $current->addMonth();
        }
        $report->months = $months;
        $report->duration = count($months);

        $matchResult = $this->matcher->matchFromDatabase($min, $max);
        $report->matchResult = $matchResult;

        $periodSummary = [];
        $itemSummary = [];

        $overallPoQty = 0;
        $overallReceivedQty = 0;
        $overallOutstanding = 0;
        $overallPoAmountUsd = 0;
        $overallReceivedAmountUsd = 0;

        foreach ($matchResult->matchedPairs as $match) {
            $po = $match['po'];
            $inc = $match['incoming'];
            
            $period = $inc->period_month ?? Carbon::parse($po->tanggal)->format('Y-m');
            $itemCode = $po->item_code ?? '';
            $supplier = $po->supplier ?? '';

            $poQty = $match['po_qty'];
            $receivedQty = $match['received_qty'];
            $outstanding = $match['outstanding_qty'];

            $poAmountUsd = $this->convertToUsd($po->price ?? 0, $po->currency ?? 'IDR');
            $receivedAmountUsd = $this->convertToUsd($inc->price ?? 0, $inc->currency ?? 'IDR');

            $totalPoAmt = $poQty * $poAmountUsd;
            $totalIncAmt = $receivedQty * $receivedAmountUsd;

            // Aggregate by period
            if (!isset($periodSummary[$period])) {
                $periodSummary[$period] = [
                    'po_qty' => 0, 'received_qty' => 0, 'outstanding_qty' => 0,
                    'po_amount_usd' => 0, 'received_amount_usd' => 0
                ];
            }
            $periodSummary[$period]['po_qty'] += $poQty;
            $periodSummary[$period]['received_qty'] += $receivedQty;
            $periodSummary[$period]['outstanding_qty'] += $outstanding;
            $periodSummary[$period]['po_amount_usd'] += $totalPoAmt;
            $periodSummary[$period]['received_amount_usd'] += $totalIncAmt;

            // Aggregate by item
            if (!isset($itemSummary[$itemCode])) {
                $itemSummary[$itemCode] = [
                    'item_code' => $itemCode,
                    'description' => $po->name ?? '',
                    'supplier' => $supplier,
                    'total_po_qty' => 0,
                    'total_received_qty' => 0,
                    'outstanding' => 0,
                    'amount_usd' => 0
                ];
            }
            $itemSummary[$itemCode]['total_po_qty'] += $poQty;
            $itemSummary[$itemCode]['total_received_qty'] += $receivedQty;
            $itemSummary[$itemCode]['outstanding'] += $outstanding;
            $itemSummary[$itemCode]['amount_usd'] += $totalIncAmt;

            $overallPoQty += $poQty;
            $overallReceivedQty += $receivedQty;
            $overallOutstanding += $outstanding;
            $overallPoAmountUsd += $totalPoAmt;
            $overallReceivedAmountUsd += $totalIncAmt;
        }

        foreach ($periodSummary as $p => $s) {
            $periodSummary[$p]['fulfillment_pct'] = $s['po_qty'] > 0 ? ($s['received_qty'] / $s['po_qty']) * 100 : 0;
        }

        foreach ($itemSummary as $i => $s) {
            $itemSummary[$i]['fulfillment_pct'] = $s['total_po_qty'] > 0 ? ($s['total_received_qty'] / $s['total_po_qty']) * 100 : 0;
        }

        $report->periodSummary = $periodSummary;
        $report->itemSummary = $itemSummary;
        
        $report->overallMetrics = [
            'total_po_qty' => $overallPoQty,
            'total_received_qty' => $overallReceivedQty,
            'total_outstanding' => $overallOutstanding,
            'overall_fulfillment_pct' => $overallPoQty > 0 ? ($overallReceivedQty / $overallPoQty) * 100 : 0,
            'total_po_amount_usd' => $overallPoAmountUsd,
            'total_received_amount_usd' => $overallReceivedAmountUsd,
        ];

        $report->dataAvailability = [
            'master_po' => MasterPo::exists(),
            'incoming' => PurchasingLog::exists(),
            'forecast' => false,
        ];

        return $report;
    }

    /**
     * Get the dynamic horizon (min/max period) from actual data.
     *
     * @return array
     */
    public function detectHorizon(): array
    {
        $poMinDate = MasterPo::min('tanggal');
        $poMaxDate = MasterPo::max('tanggal');

        $poMin = $poMinDate ? Carbon::parse($poMinDate)->format('Y-m') : null;
        $poMax = $poMaxDate ? Carbon::parse($poMaxDate)->format('Y-m') : null;

        $incMin = PurchasingLog::min('period_month');
        $incMax = PurchasingLog::max('period_month');

        $mins = array_filter([$poMin, $incMin]);
        $maxs = array_filter([$poMax, $incMax]);

        $min = !empty($mins) ? min($mins) : null;
        $max = !empty($maxs) ? max($maxs) : null;

        $months = [];
        $duration = 0;

        if ($min && $max) {
            $current = Carbon::parse($min . '-01');
            $endDate = Carbon::parse($max . '-01');
            while ($current <= $endDate) {
                $months[] = $current->format('Y-m');
                $current->addMonth();
            }
            $duration = count($months);
        }

        return [
            'min' => $min,
            'max' => $max,
            'months' => $months,
            'duration' => $duration
        ];
    }

    /**
     * Helper to convert to USD.
     */
    private function convertToUsd(float $price, string $currency): float
    {
        if (class_exists(\App\Services\Normalization\CurrencyNormalizer::class) && method_exists(\App\Services\Normalization\CurrencyNormalizer::class, 'convertToUsd')) {
            return \App\Services\Normalization\CurrencyNormalizer::convertToUsd($price, $currency);
        }

        // Fallback
        if (strtoupper($currency) === 'IDR' && $price > 300) {
            return $price / 16600;
        }

        return $price;
    }

    /**
     * Get reconciliation summary per period.
     *
     * @return array
     */
    public function getSummaryByPeriod(): array
    {
        return $this->reconcile()->periodSummary;
    }

    /**
     * Get reconciliation summary per item.
     *
     * @param string|null $period
     * @return array
     */
    public function getSummaryByItem(?string $period = null): array
    {
        return $this->reconcile($period, $period)->itemSummary;
    }

    /**
     * Get reconciliation summary per supplier.
     *
     * @param string|null $period
     * @return array
     */
    public function getSummaryBySupplier(?string $period = null): array
    {
        $report = $this->reconcile($period, $period);
        $supplierSummary = [];

        foreach ($report->matchResult->matchedPairs as $match) {
            $supplier = $match['po']->supplier ?? 'UNKNOWN';

            if (!isset($supplierSummary[$supplier])) {
                $supplierSummary[$supplier] = [
                    'po_qty' => 0, 'received_qty' => 0
                ];
            }
            $supplierSummary[$supplier]['po_qty'] += $match['po_qty'];
            $supplierSummary[$supplier]['received_qty'] += $match['received_qty'];
        }

        return $supplierSummary;
    }

    /**
     * Store reconciliation results in database.
     *
     * @param ReconciliationReport $report
     * @param int|null $documentId
     * @param int|null $sessionId
     * @return int
     */
    public function storeResults(ReconciliationReport $report, ?int $documentId = null, ?int $sessionId = null): int
    {
        // Placeholder for storing to DB
        // Clear old results and insert new ones
        return 0;
    }
}
