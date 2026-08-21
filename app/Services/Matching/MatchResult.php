<?php

namespace App\Services\Matching;

class MatchResult
{
    /**
     * Array of matched PO and Incoming records.
     * Elements should look like: ['po' => ..., 'incoming' => ..., 'level' => 1, 'confidence' => 100]
     *
     * @var array
     */
    public array $matchedPairs = [];

    /**
     * PO records with no incoming match.
     *
     * @var array
     */
    public array $unmatchedPos = [];

    /**
     * Incoming records with no PO match.
     *
     * @var array
     */
    public array $unplannedIncomings = [];

    /**
     * Summary of the match result.
     * e.g., ['total_po' => x, 'total_incoming' => y, 'matched' => z, ...]
     *
     * @var array
     */
    public array $summary = [];

    public function __construct(
        array $matchedPairs = [],
        array $unmatchedPos = [],
        array $unplannedIncomings = [],
        array $summary = []
    ) {
        $this->matchedPairs = $matchedPairs;
        $this->unmatchedPos = $unmatchedPos;
        $this->unplannedIncomings = $unplannedIncomings;
        $this->summary = $summary;
    }
}
