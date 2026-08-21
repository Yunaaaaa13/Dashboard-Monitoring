<?php

namespace App\Services\Matching;

use App\Models\MasterPo;
use App\Models\PurchasingLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PoIncomingMatcher
{
    /**
     * Match PO records against Incoming records.
     * Returns matched pairs, unmatched POs, and unplanned incomings.
     *
     * @param Collection $poRecords
     * @param Collection $incomingRecords
     * @return MatchResult
     */
    public function match(Collection $poRecords, Collection $incomingRecords): MatchResult
    {
        $matchedPairs = [];
        
        $poPool = $poRecords->keyBy('id');
        $incPool = $incomingRecords->keyBy('id');

        // Level 1: Exact Match (Confidence 100%)
        // Key: po_number + item_code
        foreach ($poPool as $poId => $po) {
            $poNum = strtoupper(trim($po->po ?? ''));
            $poItem = strtoupper(trim($po->item_code ?? ''));

            foreach ($incPool as $incId => $inc) {
                $incPoRef = strtoupper(trim($inc->po_reference ?? ''));
                $incItem = strtoupper(trim($inc->item_code ?? ''));

                if ($poNum !== '' && $poNum === $incPoRef && $poItem !== '' && $poItem === $incItem) {
                    $matchedPairs[] = $this->createMatchPair($po, $inc, 1, 100);
                    $poPool->forget($poId);
                    $incPool->forget($incId);
                    break;
                }
            }
        }

        // Level 2: Context Match (Confidence 85%)
        // Key: item_code + supplier + period(YYYY-MM)
        foreach ($poPool as $poId => $po) {
            $poItem = strtoupper(trim($po->item_code ?? ''));
            $poSupplier = strtoupper(trim($po->supplier ?? ''));
            $poPeriod = $po->tanggal ? Carbon::parse($po->tanggal)->format('Y-m') : '';

            foreach ($incPool as $incId => $inc) {
                $incItem = strtoupper(trim($inc->item_code ?? ''));
                $incSupplier = strtoupper(trim($inc->supplier_name ?? ''));
                $incPeriod = $inc->period_month ?? '';

                if ($poItem !== '' && $poItem === $incItem && $poPeriod !== '' && $poPeriod === $incPeriod) {
                    similar_text($poSupplier, $incSupplier, $percent);
                    if ($percent >= 80 || str_contains($poSupplier, $incSupplier) || str_contains($incSupplier, $poSupplier)) {
                        $matchedPairs[] = $this->createMatchPair($po, $inc, 2, 85);
                        $poPool->forget($poId);
                        $incPool->forget($incId);
                        break;
                    }
                }
            }
        }

        // Level 3: Fallback Match (Confidence 70%)
        // Key: item_code + period(YYYY-MM)
        foreach ($poPool as $poId => $po) {
            $poItem = strtoupper(trim($po->item_code ?? ''));
            $poPeriod = $po->tanggal ? Carbon::parse($po->tanggal)->format('Y-m') : '';

            foreach ($incPool as $incId => $inc) {
                $incItem = strtoupper(trim($inc->item_code ?? ''));
                $incPeriod = $inc->period_month ?? '';

                if ($poItem !== '' && $poItem === $incItem && $poPeriod !== '' && $poPeriod === $incPeriod) {
                    $matchedPairs[] = $this->createMatchPair($po, $inc, 3, 70);
                    $poPool->forget($poId);
                    $incPool->forget($incId);
                    break;
                }
            }
        }

        $summary = [
            'total_po' => $poRecords->count(),
            'total_incoming' => $incomingRecords->count(),
            'matched' => count($matchedPairs),
            'unmatched_po' => $poPool->count(),
            'unplanned_incoming' => $incPool->count(),
        ];

        return new MatchResult(
            $matchedPairs,
            $poPool->values()->toArray(),
            $incPool->values()->toArray(),
            $summary
        );
    }

    /**
     * Create a structured matched pair.
     */
    private function createMatchPair($po, $inc, int $level, int $confidence): array
    {
        $poQty = (float)($po->qty ?? 0);
        $receivedQty = (float)($inc->actual_received ?? 0);
        $outstandingQty = $poQty - $receivedQty;

        $fulfillmentPct = 0;
        if ($poQty > 0) {
            $fulfillmentPct = ($receivedQty / $poQty) * 100;
        }
        if ($fulfillmentPct > 999) {
            $fulfillmentPct = 999;
        }

        $status = 'PARTIAL';
        if ($receivedQty == 0 || $fulfillmentPct == 0) {
            $status = 'NOT_RECEIVED';
        } elseif ($fulfillmentPct >= 95 && $fulfillmentPct <= 105) {
            $status = 'FULFILLED';
        } elseif ($fulfillmentPct > 105) {
            $status = 'OVER_RECEIVED';
        }

        return [
            'po' => $po,
            'incoming' => $inc,
            'level' => $level,
            'confidence' => $confidence,
            'po_qty' => $poQty,
            'received_qty' => $receivedQty,
            'outstanding_qty' => $outstandingQty,
            'fulfillment_pct' => $fulfillmentPct,
            'status' => $status,
        ];
    }

    /**
     * Match records from database for a given period range.
     *
     * @param string|null $periodMin
     * @param string|null $periodMax
     * @return MatchResult
     */
    public function matchFromDatabase(?string $periodMin = null, ?string $periodMax = null): MatchResult
    {
        $poQuery = MasterPo::query();
        $incQuery = PurchasingLog::query();

        if ($periodMin) {
            $poQuery->where('tanggal', '>=', $periodMin . '-01');
            $incQuery->where('period_month', '>=', $periodMin);
        }

        if ($periodMax) {
            $poQuery->where('tanggal', '<=', Carbon::parse($periodMax . '-01')->endOfMonth()->format('Y-m-d'));
            $incQuery->where('period_month', '<=', $periodMax);
        }

        $poRecords = $poQuery->get();
        $incomingRecords = $incQuery->get();

        return $this->match($poRecords, $incomingRecords);
    }
}
