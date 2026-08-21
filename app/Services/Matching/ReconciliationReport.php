<?php

namespace App\Services\Matching;

class ReconciliationReport
{
    /** @var string */
    public string $periodMin = '';

    /** @var string */
    public string $periodMax = '';

    /**
     * Ordered list of YYYY-MM periods.
     *
     * @var array<string>
     */
    public array $months = [];

    /**
     * Number of months.
     *
     * @var int
     */
    public int $duration = 0;

    /** @var MatchResult|null */
    public ?MatchResult $matchResult = null;

    /**
     * Per-period aggregates keyed by YYYY-MM.
     * {po_qty, received_qty, outstanding_qty, fulfillment_pct, po_amount_usd, received_amount_usd}
     *
     * @var array
     */
    public array $periodSummary = [];

    /**
     * Per-item aggregates keyed by item_code.
     * {item_code, description, supplier, total_po_qty, total_received_qty, outstanding, fulfillment_pct, amount_usd}
     *
     * @var array
     */
    public array $itemSummary = [];

    /**
     * Overall metrics.
     * {total_po_qty, total_received_qty, total_outstanding, overall_fulfillment_pct, total_po_amount_usd, total_received_amount_usd}
     *
     * @var array
     */
    public array $overallMetrics = [];

    /**
     * Data availability flags.
     * {master_po: true, incoming: true, forecast: false, ...}
     *
     * @var array
     */
    public array $dataAvailability = [];
}
