<?php

namespace App\Services\Document;

use App\Models\Document;

class DocumentProfiler
{
    /**
     * Profiles a document based on its parsed rows.
     *
     * @param Document $document
     * @param array $parsedRows Array of parsed rows (key-value pairs mapped to canonical fields)
     * @param array $columnMapping Array of column mappings
     * @return array
     */
    public function profile(Document $document, array $parsedRows, array $columnMapping): array
    {
        $totalRows = count($parsedRows);
        $totalColumns = count($columnMapping);
        
        $dates = [];
        $items = [];
        $pos = [];
        $suppliers = [];
        $currencies = [];
        
        $missingValues = [];
        $dataTypesDetected = [];
        $seenRows = [];
        $duplicateCandidates = 0;

        foreach ($parsedRows as $row) {
            $rowHash = md5(json_encode($row));
            if (isset($seenRows[$rowHash])) {
                $duplicateCandidates++;
            }
            $seenRows[$rowHash] = true;

            foreach ($columnMapping as $mapping) {
                $field = $mapping['canonical_field'] ?? $mapping; 
                $value = $row[$field] ?? null;

                if ($value === null || $value === '') {
                    $missingValues[$field] = ($missingValues[$field] ?? 0) + 1;
                } else {
                    if (!isset($dataTypesDetected[$field])) {
                        if (is_numeric($value)) {
                            $dataTypesDetected[$field] = is_float($value + 0) ? 'float' : 'integer';
                        } elseif (strtotime((string)$value) !== false) {
                            $dataTypesDetected[$field] = 'date';
                        } else {
                            $dataTypesDetected[$field] = 'string';
                        }
                    }
                }
            }

            if (!empty($row['period'])) {
                $dates[] = $row['period'];
            } elseif (!empty($row['date'])) {
                $dates[] = date('Y-m', strtotime($row['date']));
            }
            
            if (!empty($row['item_code'])) {
                $items[] = $row['item_code'];
            }
            
            if (!empty($row['po_number'])) {
                $pos[] = $row['po_number'];
            }
            
            if (!empty($row['supplier'])) {
                $suppliers[] = $row['supplier'];
            }
            
            if (!empty($row['currency'])) {
                $currencies[] = mb_strtoupper($row['currency']);
            }
        }

        $uniqueDates = array_filter(array_unique($dates));
        sort($uniqueDates);
        
        $currencyDistribution = [];
        if (!empty($currencies)) {
            $currencyCounts = array_count_values($currencies);
            $totalCurrencies = count($currencies);
            foreach ($currencyCounts as $currency => $count) {
                $currencyDistribution[$currency] = round(($count / $totalCurrencies) * 100, 2);
            }
        }

        $profile = [
            'total_rows' => $totalRows,
            'total_columns' => $totalColumns,
            'date_range_min' => !empty($uniqueDates) ? $uniqueDates[0] : null,
            'date_range_max' => !empty($uniqueDates) ? end($uniqueDates) : null,
            'unique_items' => count(array_unique($items)),
            'unique_pos' => count(array_unique($pos)),
            'unique_suppliers' => count(array_unique($suppliers)),
            'currency_distribution' => $currencyDistribution,
            'missing_values' => $missingValues,
            'duplicate_candidates' => $duplicateCandidates,
            'data_types_detected' => $dataTypesDetected,
        ];

        // Update the document model
        $document->update([
            'total_rows' => $profile['total_rows'],
            'total_columns' => $profile['total_columns'],
            'date_range_min' => $profile['date_range_min'],
            'date_range_max' => $profile['date_range_max'],
            'unique_items' => $profile['unique_items'],
            'unique_pos' => $profile['unique_pos'],
            'unique_suppliers' => $profile['unique_suppliers'],
            'currency_distribution' => $profile['currency_distribution'],
            'profile_data' => $profile,
        ]);

        return $profile;
    }
}
