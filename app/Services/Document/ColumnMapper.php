<?php

namespace App\Services\Document;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Service to map source Excel columns to canonical schema fields.
 */
class ColumnMapper
{
    public const CANONICAL_FIELDS = [
        'supplier_code', 'supplier_name', 'delivery_date', 'item_code',
        'description', 'po_number', 'currency', 'unit_price', 'plant',
        'plan_qty', 'plan_amount', 'result_qty', 'result_amount',
        'remaining_qty', 'remaining_amount',
    ];

    private const ALIAS_DICTIONARY = [
        'po_number' => ['PO NO', 'PO NUMBER', 'NO PO', 'NOMOR PO', 'PO', 'PURCHASE ORDER', 'NO. PO', 'PO REF', 'PO REFERENCE', 'NO PO.', 'NOMER PO'],
        'item_code' => ['MATERIAL CODE', 'ITEM CODE', 'ITEM_CODE', 'PART NUMBER', 'PART NO', 'PART_NO', 'PN', 'DRAWING', 'KODE BARANG', 'KODE ITEM', 'KODE MATERIAL', 'NO MATERIAL', 'MATERIAL NO', 'MAT CODE', 'MATERIAL', 'ITEM', 'PART', 'ITEMCODE'],
        'description' => ['DESCRIPTION', 'DESKRIPSI', 'NAMA BARANG', 'ITEM NAME', 'NAMA MATERIAL', 'DESC', 'NAMA ITEM', 'NAMA PART', 'ITEM DESCRIPTION', 'MATERIAL DESCRIPTION', 'PART NAME'],
        'supplier_code' => ['SUPPLIER CODE', 'VENDOR CODE', 'KODE SUPPLIER', 'VEND CODE', 'SUPP CODE', 'VEND_CODE', 'SUPP_CODE', 'SUPPLIER_CODE'],
        'supplier_name' => ['SUPPLIER NAME', 'VENDOR NAME', 'NAMA SUPPLIER', 'NAMA VENDOR', 'SUPPLIER', 'VENDOR', 'PEMASOK'],
        'delivery_date' => ['DELIVERY DATE', 'TANGGAL', 'DATE', 'TGL KIRIM', 'RECEIPT DATE', 'TANGGAL PO', 'PO DATE', 'TGL', 'RECEIVING DATE', 'TGL TERIMA', 'TANGGAL KIRIM', 'TANGGAL TERIMA'],
        'currency' => ['CURRENCY', 'MATA UANG', 'KURS', 'CURR', 'CUR', 'CCY'],
        'unit_price' => ['UNIT PRICE', 'PRICE', 'HARGA', 'HARGA SATUAN', 'UP', 'HARGA/UNIT'],
        'plant' => ['PLANT', 'FACTORY', 'PABRIK', 'KODE PABRIK', 'FACTORY CODE'],
        'plan_qty' => ['PLAN QTY', 'PLAN', 'TARGET', 'PO QTY', 'ORDER QTY', 'QTY PO', 'QTY ORDER', 'JUMLAH ORDER', 'JUMLAH PO', 'QTY PLAN', 'TARGET QTY'],
        'plan_amount' => ['PLAN AMOUNT', 'PLAN AMT', 'PLAN VALUE', 'PLAN TOTAL', 'PO AMOUNT', 'ORDER AMOUNT', 'TARGET AMOUNT', 'NILAI PO', 'NILAI ORDER'],
        'result_qty' => ['RESULT QTY', 'RESULT', 'ACTUAL QTY', 'ACTUAL', 'RECEIVED QTY', 'RECEIVED', 'INCOMING QTY', 'INCOMING', 'REALISASI', 'QTY RECEIVED', 'QTY ACTUAL', 'QTY INCOMING', 'DITERIMA', 'JML DITERIMA', 'JUMLAH DITERIMA', 'ACTUAL RECEIVED'],
        'result_amount' => ['RESULT AMOUNT', 'RESULT AMT', 'ACTUAL AMOUNT', 'RECEIVED AMOUNT', 'INCOMING AMOUNT', 'NILAI ACTUAL', 'NILAI DITERIMA'],
        'remaining_qty' => ['REMAINING', 'REMAINING QTY', 'OUTSTANDING', 'SISA', 'REM', 'OUTSTANDING QTY', 'SISA QTY', 'QTY SISA'],
        'remaining_amount' => ['REMAINING AMOUNT', 'REM AMOUNT', 'OUTSTANDING AMOUNT', 'SISA AMOUNT', 'NILAI SISA'],
    ];

    /**
     * Map header columns to canonical fields.
     *
     * @param array $headerRow
     * @param array|null $aboveRow
     * @param string|null $documentType
     * @return array
     */
    public function map(array $headerRow, ?array $aboveRow = null, ?string $documentType = null): array
    {
        $mappings = [];
        $unmappedColumns = [];
        $mappedFields = [];
        
        $totalConfidence = 0;
        $mappedCount = 0;

        foreach ($headerRow as $index => $sourceName) {
            $colLetter = $this->indexToColumnLetter($index);
            $cleanName = trim((string)$sourceName);
            
            if (empty($cleanName)) {
                continue;
            }

            $match = $this->findBestMatch($cleanName, $aboveRow[$index] ?? null, $colLetter, $documentType, $mappedFields);
            
            if ($match) {
                $mappings[] = [
                    'column_letter' => $colLetter,
                    'source_name' => $cleanName,
                    'canonical_field' => $match['field'],
                    'confidence' => $match['confidence'],
                    'method' => $match['method'],
                ];
                $mappedFields[] = $match['field'];
                $totalConfidence += $match['confidence'];
                $mappedCount++;
            } else {
                $unmappedColumns[] = $colLetter;
            }
        }

        $missingRequired = array_diff(['item_code', 'po_number', 'plan_qty'], $mappedFields);
        
        // Document type specific required fields
        if ($documentType === 'INCOMING' || $documentType === 'INTEGRATED') {
            if (!in_array('result_qty', $mappedFields)) {
                $missingRequired[] = 'result_qty';
            }
        }

        return [
            'mappings' => $mappings,
            'unmapped_columns' => $unmappedColumns,
            'missing_required' => array_values($missingRequired),
            'overall_confidence' => $mappedCount > 0 ? ($totalConfidence / $mappedCount) : 0.0,
        ];
    }

    /**
     * Learn an alias mapping and save to database.
     *
     * @param string $rawName
     * @param string $canonicalField
     * @return void
     */
    public function learnFromConfirmation(string $rawName, string $canonicalField): void
    {
        if (empty(trim($rawName)) || !in_array($canonicalField, self::CANONICAL_FIELDS)) {
            return;
        }

        $cleanName = strtoupper(trim($rawName));

        // Assuming column_aliases table structure: id, raw_name, canonical_field, usage_count, created_at, updated_at
        if (Schema::hasTable('column_aliases')) {
            $existing = DB::table('column_aliases')
                ->where('raw_name', $cleanName)
                ->where('canonical_field', $canonicalField)
                ->first();

            if ($existing) {
                DB::table('column_aliases')
                    ->where('id', $existing->id)
                    ->increment('usage_count');
            } else {
                DB::table('column_aliases')->insert([
                    'raw_name' => $cleanName,
                    'canonical_field' => $canonicalField,
                    'usage_count' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Convert zero-based index to Excel column letter (A, B, C...)
     *
     * @param int $index
     * @return string
     */
    private function indexToColumnLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr($index % 26 + 65) . $letter;
            $index = (int)($index / 26) - 1;
        }
        return $letter;
    }

    /**
     * Find best match for a column name.
     *
     * @param string $sourceName
     * @param string|null $aboveValue
     * @param string $colLetter
     * @param string|null $documentType
     * @param array $alreadyMapped
     * @return array|null
     */
    private function findBestMatch(string $sourceName, ?string $aboveValue, string $colLetter, ?string $documentType, array $alreadyMapped): ?array
    {
        $upperSource = strtoupper(trim($sourceName));
        $upperCombined = strtoupper(trim(($aboveValue ?? '') . ' ' . $sourceName));

        // 1. Check DB Aliases (if table exists)
        if (Schema::hasTable('column_aliases')) {
            $dbMatch = DB::table('column_aliases')
                ->where('raw_name', $upperSource)
                ->orderByDesc('usage_count')
                ->first();
                
            if ($dbMatch && !in_array($dbMatch->canonical_field, $alreadyMapped)) {
                return ['field' => $dbMatch->canonical_field, 'confidence' => 99.0, 'method' => 'AUTO'];
            }
        }

        // 2. Exact match in dictionary
        foreach (self::ALIAS_DICTIONARY as $field => $aliases) {
            if (in_array($upperSource, $aliases) && !in_array($field, $alreadyMapped)) {
                return ['field' => $field, 'confidence' => 95.0, 'method' => 'ALIAS'];
            }
        }

        // 3. Keyword match (partial)
        foreach (self::ALIAS_DICTIONARY as $field => $aliases) {
            if (in_array($field, $alreadyMapped)) {
                continue;
            }
            foreach ($aliases as $alias) {
                if (str_contains($upperSource, $alias)) {
                    return ['field' => $field, 'confidence' => 85.0, 'method' => 'KEYWORD'];
                }
            }
        }

        // 4. Combined header match
        if (!empty($aboveValue)) {
            foreach (self::ALIAS_DICTIONARY as $field => $aliases) {
                if (in_array($field, $alreadyMapped)) {
                    continue;
                }
                foreach ($aliases as $alias) {
                    if (str_contains($upperCombined, $alias)) {
                        return ['field' => $field, 'confidence' => 80.0, 'method' => 'COMBINED'];
                    }
                }
            }
        }

        // 5. Positional fallback (very basic heuristic)
        if ($documentType === 'MASTER_PO') {
            if ($colLetter === 'A' && !in_array('po_number', $alreadyMapped)) {
                // Not ideal, but as an example
                // return ['field' => 'po_number', 'confidence' => 50.0, 'method' => 'POSITIONAL'];
            }
        }

        return null;
    }
}
