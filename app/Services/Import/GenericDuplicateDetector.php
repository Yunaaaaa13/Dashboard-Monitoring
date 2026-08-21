<?php

namespace App\Services\Import;

use App\Models\MasterPo;
use App\Models\PurchasingLog;
use Illuminate\Support\Facades\DB;

class GenericDuplicateDetector
{
    /**
     * Detect if a file is duplicated based on SHA-256 hash.
     *
     * @param string $filePath
     * @return int|null Document ID if found, null otherwise
     */
    public function detectFileDuplicate(string $filePath): ?int
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $hash = hash_file('sha256', $filePath);
        
        // Assuming a `documents` table exists with `file_hash`
        $document = DB::table('documents')->where('file_hash', $hash)->first();
        
        return $document ? (int) $document->id : null;
    }

    /**
     * Detect duplicate rows within the file itself.
     *
     * @param array $rows
     * @param string $documentType
     * @return array
     */
    public function detectRowDuplicates(array $rows, string $documentType): array
    {
        $uniqueRows = [];
        $duplicateGroups = [];
        $duplicateCount = 0;
        $seenKeys = [];

        foreach ($rows as $index => $row) {
            $key = $this->generateBusinessKey($row, $documentType);

            if (isset($seenKeys[$key])) {
                $firstIndex = $seenKeys[$key];
                
                if (!isset($duplicateGroups[$key])) {
                    $duplicateGroups[$key] = [
                        'key' => $key,
                        'rows' => [$uniqueRows[$firstIndex]],
                        'action' => 'SKIP_DUPLICATE'
                    ];
                }
                
                $duplicateGroups[$key]['rows'][] = $row;
                $duplicateCount++;
            } else {
                $seenKeys[$key] = count($uniqueRows);
                $uniqueRows[] = $row;
            }
        }

        return [
            'unique_rows' => $uniqueRows,
            'duplicate_groups' => array_values($duplicateGroups),
            'duplicate_count' => $duplicateCount,
        ];
    }

    /**
     * Detect if rows already exist in the database.
     *
     * @param array $rows
     * @param string $documentType
     * @return array Array of rows that already exist in DB
     */
    public function detectDatabaseDuplicates(array $rows, string $documentType): array
    {
        $duplicates = [];

        foreach ($rows as $row) {
            if ($documentType === 'MASTER_PO') {
                $exists = MasterPo::where('po', $row['po'] ?? '')
                    ->where('item_code', $row['item_code'] ?? '')
                    ->exists();
                
                if ($exists) {
                    $duplicates[] = $row;
                }
            } elseif ($documentType === 'INCOMING') {
                $exists = PurchasingLog::where('po_reference', $row['po_reference'] ?? '')
                    ->where('item_code', $row['item_code'] ?? '')
                    ->where('receipt_date', $row['receipt_date'] ?? '')
                    ->exists();
                
                if ($exists) {
                    $duplicates[] = $row;
                }
            }
        }

        return $duplicates;
    }

    /**
     * Generate a unique business key for a row based on document type.
     *
     * @param array $row
     * @param string $documentType
     * @return string
     */
    protected function generateBusinessKey(array $row, string $documentType): string
    {
        if ($documentType === 'MASTER_PO') {
            if (!empty($row['po'])) {
                return ($row['po'] ?? '') . '|' . ($row['item_code'] ?? '');
            }
            return ($row['item_code'] ?? '') . '|' . ($row['supplier'] ?? '') . '|' . ($row['date'] ?? '');
        }

        if ($documentType === 'INCOMING') {
            return ($row['po_reference'] ?? '') . '|' . 
                   ($row['item_code'] ?? '') . '|' . 
                   ($row['receipt_date'] ?? '') . '|' . 
                   ($row['received_qty'] ?? '');
        }

        if ($documentType === 'FORECAST') {
            return ($row['item_code'] ?? '') . '|' . ($row['period'] ?? '');
        }

        return md5(json_encode($row));
    }
}
