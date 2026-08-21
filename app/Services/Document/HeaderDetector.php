<?php

namespace App\Services\Document;

/**
 * Service to detect the header row in a spreadsheet.
 */
class HeaderDetector
{
    private const KEYWORD_MAP = [
        'supplier' => ['SUPPLIER', 'VENDOR', 'NAMA SUPPLIER', 'NAMA VENDOR', 'SUPPLIER NAME', 'VENDOR NAME', 'SUPP', 'VEND'],
        'date' => ['DATE', 'TANGGAL', 'TGL', 'DELIVERY DATE', 'PO DATE', 'RECEIPT DATE', 'TGL KIRIM', 'TANGGAL PO', 'RECEIVING DATE'],
        'item_code' => ['MATERIAL', 'ITEM CODE', 'ITEM_CODE', 'PART NUMBER', 'PART NO', 'PART_NO', 'PN', 'DRAWING', 'MATERIAL CODE', 'KODE BARANG', 'KODE ITEM', 'KODE MATERIAL', 'NO MATERIAL', 'MATERIAL NO', 'MAT CODE'],
        'description' => ['DESCRIPTION', 'DESKRIPSI', 'NAMA BARANG', 'ITEM NAME', 'NAMA MATERIAL', 'DESC', 'NAMA ITEM', 'NAMA PART'],
        'po_number' => ['PO NO', 'PO NUMBER', 'NO PO', 'NOMOR PO', 'PO', 'PURCHASE ORDER', 'NO. PO', 'PO REF'],
        'currency' => ['CURRENCY', 'MATA UANG', 'KURS', 'CURR', 'CUR', 'CCY'],
        'price' => ['PRICE', 'HARGA', 'UNIT PRICE', 'HARGA SATUAN', 'UP'],
        'quantity' => ['QTY', 'QUANTITY', 'JUMLAH', 'ORDER QTY', 'QTY ORDER', 'PLAN', 'TARGET', 'RESULT', 'ACTUAL', 'RECEIVED', 'INCOMING', 'REALISASI'],
        'amount' => ['AMOUNT', 'TOTAL', 'NILAI', 'AMT', 'VALUE'],
        'plant' => ['PLANT', 'FACTORY', 'PABRIK', 'KODE PABRIK'],
        'remaining' => ['REMAINING', 'OUTSTANDING', 'SISA', 'REM'],
    ];

    /**
     * Detect which row is the actual header row.
     *
     * @param array $rawRows
     * @param int $maxScanRows
     * @return array
     */
    public function detect(array $rawRows, int $maxScanRows = 20): array
    {
        $bestRowIndex = 0;
        $highestScore = -1;
        
        $scanLimit = min(count($rawRows), $maxScanRows);
        
        for ($i = 0; $i < $scanLimit; $i++) {
            $row = $rawRows[$i];
            
            if (!is_array($row)) {
                continue;
            }

            $score = $this->calculateHeaderScore($row);
            
            // Look ahead to the next row to see if it is data
            if (isset($rawRows[$i + 1]) && is_array($rawRows[$i + 1])) {
                $nextRow = $rawRows[$i + 1];
                if ($this->isDataRow($nextRow)) {
                    $score += 30; // Strong indicator if the next row is data
                } else {
                    $score -= 10;
                }
            }
            
            if ($score > $highestScore) {
                $highestScore = $score;
                $bestRowIndex = $i;
            }
        }
        
        // Extract metadata from rows above the header
        $metadataRows = [];
        for ($i = 0; $i < $bestRowIndex; $i++) {
            if (isset($rawRows[$i]) && is_array($rawRows[$i])) {
                $metadataRows[] = array_filter($rawRows[$i], fn($val) => !is_null($val) && $val !== '');
            }
        }
        
        return [
            'header_row_index' => $bestRowIndex,
            'data_start_index' => $bestRowIndex + 1,
            'confidence' => min(max($highestScore, 0), 100),
            'metadata_rows' => $metadataRows,
        ];
    }

    /**
     * Calculate a likelihood score that a given row is a header row.
     *
     * @param array $row
     * @return float
     */
    private function calculateHeaderScore(array $row): float
    {
        $score = 0;
        $nonEmptyCount = 0;
        $stringCount = 0;
        $numericCount = 0;
        
        foreach ($row as $cell) {
            $val = trim((string)$cell);
            
            if ($val !== '') {
                $nonEmptyCount++;
                
                if (is_numeric($val) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
                    $numericCount++;
                    $score -= 5; // Headers typically don't have purely numeric or date values
                } else {
                    $stringCount++;
                    $upperVal = strtoupper($val);
                    
                    // Check against keyword map
                    foreach (self::KEYWORD_MAP as $category => $keywords) {
                        foreach ($keywords as $keyword) {
                            if (str_contains($upperVal, $keyword)) {
                                $score += 10;
                                break; // Match once per category for this cell
                            }
                        }
                    }
                }
            }
        }
        
        // Bonus for having multiple non-empty string cells
        if ($nonEmptyCount > 2) {
            $score += ($nonEmptyCount * 2);
        }
        
        // Penalty if there are more numeric cells than string cells
        if ($numericCount > $stringCount) {
            $score -= 20;
        }
        
        return (float)$score;
    }

    /**
     * Check if a row looks like a data row (contains numbers, dates, etc.).
     *
     * @param array $row
     * @return bool
     */
    private function isDataRow(array $row): bool
    {
        $numericCount = 0;
        $nonEmptyCount = 0;
        
        foreach ($row as $cell) {
            $val = trim((string)$cell);
            if ($val !== '') {
                $nonEmptyCount++;
                if (is_numeric($val) || preg_match('/^\d{4}-\d{2}-\d{2}|\d{2}\/\d{2}\/\d{4}/', $val)) {
                    $numericCount++;
                }
            }
        }
        
        // If at least a third of the non-empty cells are numeric/dates, we consider it a data row
        return $nonEmptyCount > 0 && ($numericCount / $nonEmptyCount) > 0.3;
    }
}
