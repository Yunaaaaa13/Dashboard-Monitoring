<?php

namespace App\Services\Ocr;

/**
 * OcrQualityScorer
 * 
 * Mesin penskoran kualitas dan deteksi anomali karakter OCR scanning.
 * Mendeteksi potensi salah baca (0 vs O, 1 vs I, 5 vs S, 8 vs B) dan
 * memberikan skor keyakinan (Confidence Scoring) bertingkat.
 */
class OcrQualityScorer
{
    /**
     * Skor Kualitas Satu Baris Data OCR
     * 
     * @param array $row [ 'material_code' => string, 'qty' => mixed, 'supplier' => string, ... ]
     * @param MasterDictionaryMatcher $matcher
     * @return array [ 'row_score' => float, 'tier' => string, 'field_scores' => array, 'suggestions' => array, 'needs_review' => bool ]
     */
    public static function scoreRow(array $row, MasterDictionaryMatcher $matcher): array
    {
        $fieldScores = [];
        $suggestions = [];
        $penalties = 0;

        // 1. Evaluasi Part Number / Material Code
        $matRaw = (string) ($row['material_code'] ?? $row['item_code'] ?? ($row[3] ?? ''));
        $matClean = trim($matRaw);
        if ($matClean === '') {
            $fieldScores['material_code'] = 0.0;
            $penalties += 40;
        } else {
            // Cek anomali angka dan huruf membingungkan
            $matScore = 1.0;
            if (preg_match('/[oO]/', $matClean) && preg_match('/\d/', $matClean)) {
                // Potensi O vs 0
                $matScore -= 0.15;
            }
            $matMatch = $matcher->matchMaterialCode($matClean);
            if ($matMatch['is_exact']) {
                $matScore = 1.0;
            } elseif ($matMatch['matched'] !== null) {
                $matScore = max(0.70, $matMatch['similarity']);
                $suggestions['material_code'] = $matMatch['matched'];
            } else {
                $matScore -= 0.20;
            }
            $fieldScores['material_code'] = round(max(0, $matScore), 2);
        }

        // 2. Evaluasi Quantity (Harus murni angka non-negatif)
        $qtyRaw = $row['qty'] ?? $row['production_qty'] ?? $row['actual_inventory'] ?? ($row[5] ?? null);
        if ($qtyRaw === null || trim((string)$qtyRaw) === '') {
            $fieldScores['qty'] = 0.0;
            $penalties += 35;
        } elseif (is_numeric($qtyRaw)) {
            $fieldScores['qty'] = 1.0;
        } else {
            // Analisis karakter OCR yang tertukar
            $str = (string) $qtyRaw;
            $corrected = preg_replace('/[oO]/', '0', $str);
            $corrected = preg_replace('/[iIlL]/', '1', $corrected);
            $corrected = preg_replace('/[sS]/', '5', $corrected);
            $corrected = preg_replace('/[bB]/', '8', $corrected);
            $corrected = preg_replace('/[zZ]/', '2', $corrected);

            if (is_numeric($corrected)) {
                $fieldScores['qty'] = 0.75;
                $suggestions['qty'] = (float) $corrected;
            } else {
                $fieldScores['qty'] = 0.20;
                $penalties += 25;
            }
        }

        // 3. Evaluasi Supplier Name
        $suppRaw = (string) ($row['supplier_name'] ?? $row['supplier'] ?? ($row[1] ?? ''));
        if (trim($suppRaw) !== '') {
            $suppMatch = $matcher->matchSupplier($suppRaw);
            if ($suppMatch['is_exact']) {
                $fieldScores['supplier'] = 1.0;
            } elseif ($suppMatch['matched'] !== null) {
                $fieldScores['supplier'] = max(0.80, $suppMatch['similarity']);
                if ($suppMatch['matched'] !== $suppRaw) {
                    $suggestions['supplier'] = $suppMatch['matched'];
                }
            } else {
                // Nama supplier valid teks bersih
                $fieldScores['supplier'] = preg_match('/^[A-Z0-9\.\s\-\,\(\)]+$/i', $suppRaw) ? 0.95 : 0.70;
            }
        } else {
            $fieldScores['supplier'] = 0.90; // Optional field
        }

        // 4. Hitung Skor Rata-rata Baris
        $avgScore = count($fieldScores) > 0 ? (array_sum($fieldScores) / count($fieldScores)) * 100 : 0.0;
        $finalScore = max(0.0, min(100.0, round($avgScore, 1)));

        // Klasifikasi Tingkat Keyakinan (Confidence Tier)
        if ($finalScore >= 90.0 && empty($suggestions)) {
            $tier = 'HIGH_CONFIDENCE';
            $needsReview = false;
        } elseif ($finalScore >= 70.0 || !empty($suggestions)) {
            $tier = 'MEDIUM_CONFIDENCE';
            $needsReview = true;
        } else {
            $tier = 'LOW_CONFIDENCE';
            $needsReview = true;
        }

        return [
            'row_score' => $finalScore,
            'tier' => $tier,
            'field_scores' => $fieldScores,
            'suggestions' => $suggestions,
            'needs_review' => $needsReview,
        ];
    }

    /**
     * Skor Keseluruhan Batch OCR
     */
    public static function scoreBatch(array $rows, MasterDictionaryMatcher $matcher): array
    {
        $highCount = 0;
        $mediumCount = 0;
        $lowCount = 0;
        $scoredRows = [];
        $totalScore = 0.0;

        foreach ($rows as $idx => $row) {
            $res = self::scoreRow($row, $matcher);
            $scoredRows[$idx] = $res;
            $totalScore += $res['row_score'];

            if ($res['tier'] === 'HIGH_CONFIDENCE') {
                $highCount++;
            } elseif ($res['tier'] === 'MEDIUM_CONFIDENCE') {
                $mediumCount++;
            } else {
                $lowCount++;
            }
        }

        $totalRows = count($rows);
        $avgQuality = $totalRows > 0 ? round($totalScore / $totalRows, 1) : 100.0;

        return [
            'total_rows' => $totalRows,
            'average_quality' => $avgQuality,
            'high_confidence_count' => $highCount,
            'medium_confidence_count' => $mediumCount,
            'low_confidence_count' => $lowCount,
            'high_confidence_pct' => $totalRows > 0 ? round(($highCount / $totalRows) * 100, 1) : 100.0,
            'scored_rows' => $scoredRows,
        ];
    }
}
