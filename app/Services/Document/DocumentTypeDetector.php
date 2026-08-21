<?php

namespace App\Services\Document;

/**
 * Service to detect the type of document uploaded.
 */
class DocumentTypeDetector
{
    private const TYPES = [
        'MASTER_PO',
        'INCOMING',
        'FORECAST',
        'INTEGRATED',
        'ACTUAL_PRODUCTION',
        'ACTUAL_STOCK'
    ];

    /**
     * Detect document type based on header row, sample data, and filename.
     *
     * @param array $headerRow
     * @param array $sampleDataRows
     * @param string|null $filenameHint
     * @return array
     */
    public function detect(array $headerRow, array $sampleDataRows = [], ?string $filenameHint = null): array
    {
        $scores = [];
        $signalsMap = [];
        
        foreach (self::TYPES as $type) {
            $scores[$type] = 0.0;
            $signalsMap[$type] = [];
        }
        
        $headerString = strtoupper(implode(' ', array_filter($headerRow)));
        $filename = strtoupper($filenameHint ?? '');

        // --- MASTER_PO signals ---
        $hasPoNum = str_contains($headerString, 'PO NO') || str_contains($headerString, 'PO NUMBER') || str_contains($headerString, 'NOMOR PO');
        $hasPlanQty = str_contains($headerString, 'PLAN') || str_contains($headerString, 'ORDER QTY') || str_contains($headerString, 'QTY ORDER');
        $hasActualQty = str_contains($headerString, 'ACTUAL') || str_contains($headerString, 'RECEIVED') || str_contains($headerString, 'INCOMING');
        
        $signalsMap['MASTER_PO']['Has PO Number column'] = $hasPoNum;
        $signalsMap['MASTER_PO']['Has Plan/Order Qty column'] = $hasPlanQty;
        $signalsMap['MASTER_PO']['Does not have Received/Actual column'] = !$hasActualQty;
        
        if ($hasPoNum) $scores['MASTER_PO'] += 30;
        if ($hasPlanQty) $scores['MASTER_PO'] += 20;
        if (!$hasActualQty) $scores['MASTER_PO'] += 20;
        if (str_contains($filename, 'PO') || str_contains($filename, 'PLAN') || str_contains($filename, 'ORDER')) {
            $scores['MASTER_PO'] += 30;
        }

        // --- INCOMING signals ---
        $hasReceiptDate = str_contains($headerString, 'RECEIPT DATE') || str_contains($headerString, 'TGL KIRIM') || str_contains($headerString, 'DELIVERY DATE');
        $signalsMap['INCOMING']['Has PO Reference'] = $hasPoNum;
        $signalsMap['INCOMING']['Has Received/Actual Qty'] = $hasActualQty;
        $signalsMap['INCOMING']['Has Receipt/Receiving Date'] = $hasReceiptDate;
        
        if ($hasPoNum) $scores['INCOMING'] += 20;
        if ($hasActualQty) $scores['INCOMING'] += 30;
        if ($hasReceiptDate) $scores['INCOMING'] += 20;
        if (str_contains($filename, 'INCOMING') || str_contains($filename, 'RECEIPT') || str_contains($filename, 'RECEIVING') || str_contains($filename, 'REALISASI') || str_contains($filename, 'ACTUAL')) {
            $scores['INCOMING'] += 30;
        }

        // --- FORECAST signals ---
        $hasForecastKeywords = preg_match('/\b(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC|M1|M2|M3)\b/i', $headerString) || str_contains($headerString, 'FORECAST');
        $signalsMap['FORECAST']['Has forecast columns'] = (bool)$hasForecastKeywords;
        
        if ($hasForecastKeywords) $scores['FORECAST'] += 50;
        if (str_contains($filename, 'FORECAST')) $scores['FORECAST'] += 50;

        // --- INTEGRATED signals ---
        $signalsMap['INTEGRATED']['Has Plan Qty column'] = $hasPlanQty;
        $signalsMap['INTEGRATED']['Has Result Qty column'] = $hasActualQty;
        
        if ($hasPlanQty && $hasActualQty) $scores['INTEGRATED'] += 50;
        if (str_contains($filename, 'INTEGRATED') || str_contains($filename, 'COMBINED')) {
            $scores['INTEGRATED'] += 50;
        }

        // --- ACTUAL_PRODUCTION signals ---
        $hasProdColumns = str_contains($headerString, 'PRODUCTION') || str_contains($headerString, 'OUTPUT') || str_contains($headerString, 'WIP') || str_contains($headerString, 'PRODUKSI');
        $signalsMap['ACTUAL_PRODUCTION']['Has production columns'] = $hasProdColumns;
        
        if ($hasProdColumns) $scores['ACTUAL_PRODUCTION'] += 50;
        if (str_contains($filename, 'PRODUCTION') || str_contains($filename, 'PRODUKSI')) {
            $scores['ACTUAL_PRODUCTION'] += 50;
        }

        // --- ACTUAL_STOCK signals ---
        $hasStockColumns = str_contains($headerString, 'STOCK') || str_contains($headerString, 'INVENTORY') || str_contains($headerString, 'WAREHOUSE') || str_contains($headerString, 'STOK');
        $signalsMap['ACTUAL_STOCK']['Has stock columns'] = $hasStockColumns;
        
        if ($hasStockColumns) $scores['ACTUAL_STOCK'] += 50;
        if (str_contains($filename, 'STOCK') || str_contains($filename, 'INVENTORY') || str_contains($filename, 'STOK')) {
            $scores['ACTUAL_STOCK'] += 50;
        }
        
        // Normalize scores to max 100
        arsort($scores);
        $bestType = array_key_first($scores);
        $confidence = min($scores[$bestType], 100.0);
        
        $alternatives = [];
        foreach ($scores as $type => $score) {
            if ($type !== $bestType && $score > 0) {
                $alternatives[] = [
                    'type' => $type,
                    'confidence' => min($score, 100.0)
                ];
            }
        }

        return [
            'type' => $bestType,
            'confidence' => (float)$confidence,
            'signals' => $signalsMap[$bestType],
            'alternatives' => $alternatives,
        ];
    }
}
