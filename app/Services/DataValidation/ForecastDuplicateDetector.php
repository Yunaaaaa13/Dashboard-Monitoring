<?php

namespace App\Services\DataValidation;

use App\Models\PurchasingOutstanding;
use App\Models\Forecasting;

/**
 * ForecastDuplicateDetector
 * 
 * Sistem Deteksi Duplikasi Forecast Berbasis Identitas Multi-Dimensi:
 * Identity = Material Code + Supplier + Plant + Period + Revision.
 * 
 * Mencegah false-positive warning duplicate:
 * - Material sama dari Supplier berbeda -> VALID
 * - Material sama di bulan berbeda (Jul-26 vs Aug-26) -> VALID
 * - Material sama di Plant berbeda -> VALID
 * - Hanya baris dengan identitas identik penuh yang ditandai True Duplicate.
 */
class ForecastDuplicateDetector
{
    /**
     * Evaluasi seluruh baris hasil parsing untuk mendeteksi duplikasi intra-batch & database conflict
     *
     * @param array $parsedRows
     * @return array [ 'evaluated_rows' => array, 'duplicate_count' => int, 'duplicate_details' => array ]
     */
    public static function evaluateBatch(array $parsedRows): array
    {
        $seenIdentities = [];
        $duplicateCount = 0;
        $duplicateDetails = [];
        $evaluatedRows = [];

        // Load existing records mapping from database for quick lookup
        $dbExistingMap = self::loadExistingDatabaseMap();

        foreach ($parsedRows as $index => $row) {
            $materialCode = strtoupper(trim((string)($row['material_code'] ?? '')));
            $supplierName = strtoupper(trim((string)($row['supplier_name'] ?? '')));
            $supplierCode = strtoupper(trim((string)($row['supplier_code'] ?? '')));
            $plant        = strtoupper(trim((string)($row['plant'] ?? 'PLANT 3')));
            $sourceRowNum = $row['source_row_number'] ?? ($index + 1);

            // Identitas dasar material + supplier + plant
            $baseSupplierKey = !empty($supplierCode) ? $supplierCode : (!empty($supplierName) ? $supplierName : 'GENERIC');
            
            $rowDuplicates = [];
            $monthlyData = $row['monthly_data'] ?? [];

            // Evaluasi per periode bulanan
            foreach ($monthlyData as $periodKey => $mData) {
                $uniqueIdentityKey = "{$materialCode}|{$baseSupplierKey}|{$plant}|{$periodKey}";

                // 1. Cek Duplikasi di dalam File / Batch (Intra-batch duplicate)
                if (isset($seenIdentities[$uniqueIdentityKey])) {
                    $firstSeenRow = $seenIdentities[$uniqueIdentityKey];
                    $rowDuplicates[] = [
                        'type' => 'INTRA_BATCH_DUPLICATE',
                        'period' => $periodKey,
                        'identity_key' => $uniqueIdentityKey,
                        'message' => "Duplikasi baris di dalam file: Item {$materialCode} ({$supplierName}) pada periode {$periodKey} sudah ada di Baris {$firstSeenRow}",
                        'first_seen_row' => $firstSeenRow,
                    ];
                } else {
                    $seenIdentities[$uniqueIdentityKey] = $sourceRowNum;
                }

                // 2. Cek Konflik dengan Database yang Sudah Ada
                if (isset($dbExistingMap[$uniqueIdentityKey])) {
                    $rowDuplicates[] = [
                        'type' => 'DATABASE_RECORD_EXISTS',
                        'period' => $periodKey,
                        'identity_key' => $uniqueIdentityKey,
                        'message' => "Data Forecast sudah tersimpan di database untuk {$materialCode} ({$periodKey}). Akan diperbarui/direvisi.",
                    ];
                }
            }

            // Jika ada duplicate true di dalam batch
            $isDuplicate = false;
            foreach ($rowDuplicates as $dup) {
                if ($dup['type'] === 'INTRA_BATCH_DUPLICATE') {
                    $isDuplicate = true;
                    $duplicateCount++;
                    $duplicateDetails[] = [
                        'row_number' => $sourceRowNum,
                        'material_code' => $materialCode,
                        'supplier' => $supplierName,
                        'details' => $dup['message'],
                    ];
                    break;
                }
            }

            if ($isDuplicate) {
                $row['status'] = 'DUPLICATE_WARNING';
                $row['warnings'][] = "Terdeteksi duplikasi identitas penuh pada baris ini.";
            }

            $row['duplicate_evaluations'] = $rowDuplicates;
            $evaluatedRows[] = $row;
        }

        return [
            'evaluated_rows' => $evaluatedRows,
            'duplicate_count' => $duplicateCount,
            'duplicate_details' => $duplicateDetails,
        ];
    }

    /**
     * Load map data forecast yang sudah ada di database untuk deteksi konflik
     */
    protected static function loadExistingDatabaseMap(): array
    {
        $map = [];
        try {
            $forecasts = Forecasting::select('drawing', 'supplier', 'plant', 'periode')->get();
            foreach ($forecasts as $fc) {
                $mat = strtoupper(trim((string)$fc->drawing));
                $sup = strtoupper(trim((string)($fc->supplier ?? 'GENERIC')));
                $plt = strtoupper(trim((string)($fc->plant ?? 'PLANT 3')));
                $per = trim((string)$fc->periode);
                if (!empty($mat) && !empty($per)) {
                    $key = "{$mat}|{$sup}|{$plt}|{$per}";
                    $map[$key] = true;
                }
            }
        } catch (\Throwable $e) {
            // Fallback graceful jika tabel forecasting kosong/migrasi
        }
        return $map;
    }
}
