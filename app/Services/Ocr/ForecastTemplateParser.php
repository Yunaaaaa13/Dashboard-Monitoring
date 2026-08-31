<?php

namespace App\Services\Ocr;

use App\Services\DataValidation\InputNormalizer;
use Carbon\Carbon;

/**
 * ForecastTemplateParser
 * 
 * Parser Berbasis Template Resmi Forecast PT Kawai Indonesia (Forecast Template v1):
 * - Membaca kolom dasar: Supplier Code, Supplier Name, Plant, Material Code, Description, Unit Price, Currency.
 * - Membaca blok bulanan 3-level (Periode Level 1 -> Section Level 2 -> Metric Level 3).
 * - Rekonsiliasi formula: Forecast Amount = Forecast Qty * Unit Price.
 * - Live Ratio recalculation: (Stock Bulan Ini / PROD Bulan Depan) * 100%.
 */
class ForecastTemplateParser
{
    protected MasterDictionaryMatcher $matcher;

    public function __construct(?MasterDictionaryMatcher $matcher = null)
    {
        $this->matcher = $matcher ?? new MasterDictionaryMatcher();
    }

    /**
     * Parse raw rows matrix (from XLSX/CSV or OCR) according to Forecast Template v1
     *
     * @param array $rawMatrix Array 2 dimensi dari spreadsheet / OCR
     * @param string $defaultCurrency
     * @return array [ 'success' => bool, 'batch_id' => string, 'parsed_rows' => array, 'summary' => array, 'periods' => array, 'errors' => array ]
     */
    public function parseTemplate(array $rawMatrix, string $defaultCurrency = 'ALL'): array
    {
        if (empty($rawMatrix) || count($rawMatrix) < 2) {
            return [
                'success' => false,
                'batch_id' => $this->generateBatchId(),
                'parsed_rows' => [],
                'summary' => ['total' => 0, 'valid' => 0, 'warnings' => 0, 'duplicates' => 0, 'errors' => 1],
                'periods' => [],
                'errors' => ['Berkas tidak memiliki data atau baris tidak mencukupi.'],
            ];
        }

        $batchId = $this->generateBatchId();
        
        // 1. Deteksi Baris Header (Level 1, Level 2, Level 3)
        $headerAnalysis = $this->detectHeaderStructure($rawMatrix);
        $headerRowIdx = $headerAnalysis['data_start_row_idx'] ?? 1;
        $columnMap = $headerAnalysis['column_map'] ?? [];
        $monthlyBlocks = $headerAnalysis['monthly_blocks'] ?? [];
        $detectedPeriods = array_keys($monthlyBlocks);

        $parsedRows = [];
        $validCount = 0;
        $warningCount = 0;
        $errorCount = 0;
        $errors = [];

        for ($r = $headerRowIdx; $r < count($rawMatrix); $r++) {
            $row = $rawMatrix[$r];
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $sourceRowNum = $r + 1;

            // Ekstraksi Kolom Dasar
            $rawSupplierCode = $this->getCellValue($row, $columnMap['supplier_code'] ?? -1);
            $rawSupplierName = $this->getCellValue($row, $columnMap['supplier_name'] ?? -1);
            $rawPlant        = $this->getCellValue($row, $columnMap['plant'] ?? -1, 'Plant 3');
            $rawCategory     = $this->getCellValue($row, $columnMap['category'] ?? -1, '');
            $rawMaterialCode = $this->getCellValue($row, $columnMap['material_code'] ?? -1);
            $rawDescription  = $this->getCellValue($row, $columnMap['description'] ?? -1);
            $rawPrice        = $this->getCellValue($row, $columnMap['unit_price'] ?? -1, '0');
            $rawCurrency     = $this->getCellValue($row, $columnMap['currency'] ?? -1, $defaultCurrency === 'ALL' ? 'USD' : $defaultCurrency);

            // Semantic Disambiguation: Jika $rawMaterialCode adalah Nama Perusahaan (CV./PT.)
            if ($this->isCompanyName((string)$rawMaterialCode)) {
                $actualSupplier = (string)$rawMaterialCode;
                $actualMaterial = (!empty($rawSupplierCode) && !$this->isCompanyName((string)$rawSupplierCode)) 
                    ? (string)$rawSupplierCode 
                    : ((!empty($rawSupplierName) && !$this->isCompanyName((string)$rawSupplierName)) ? (string)$rawSupplierName : (string)$rawDescription);
                $rawSupplierName = $actualSupplier;
                $rawMaterialCode = $actualMaterial;
            } elseif ($this->isCompanyName((string)$rawSupplierCode) && empty($rawSupplierName)) {
                $rawSupplierName = $rawSupplierCode;
                $rawSupplierCode = '';
            }

            if (empty($rawMaterialCode) && empty($rawDescription)) {
                continue; // Lewati baris dekorasi/kosong
            }

            // Normalisasi & Validasi Kamus Master Data
            $matMatch = $this->matcher->matchMaterialCode((string)$rawMaterialCode);
            $materialCode = $matMatch['is_exact'] ? $matMatch['matched'] : ($matMatch['suggested'] ?? InputNormalizer::cleanMaterialCode((string)$rawMaterialCode));
            $materialConfidence = $matMatch['similarity'] * 100;

            $supMatch = $this->matcher->matchSupplier((string)$rawSupplierName);
            $supplierName = $supMatch['is_exact'] ? $supMatch['matched'] : ($supMatch['suggested'] ?? trim((string)$rawSupplierName));
            $supplierConfidence = $supMatch['similarity'] * 100;

            $catMatch = $this->matcher->matchCategory((string)$rawCategory);
            $categoryId = $catMatch['category_id'] ?? null;
            $categoryCode = $catMatch['category_code'] ?? (string)$rawCategory;
            $categoryName = $catMatch['category_name'] ?? '';

            $currency = InputNormalizer::normalizeCurrency((string)$rawCurrency);
            $unitPrice = InputNormalizer::normalizePrice((string)$rawPrice, $currency);
            $plant = InputNormalizer::normalizePlantCode((string)$rawPlant);

            // Ekstraksi Data Bulanan (Monthly Blocks)
            $monthsData = [];
            $totalForecastQty = 0;
            $totalForecastAmount = 0.0;
            $rowWarnings = [];

            foreach ($monthlyBlocks as $periodKey => $blockCols) {
                $outstandQty = InputNormalizer::cleanQuantity($this->getCellValue($row, $blockCols['outstand_qty'] ?? -1, 0));
                $outstandAmt = $this->parseNumericAmount($this->getCellValue($row, $blockCols['outstand_amt'] ?? -1, 0), $currency);

                $stockQty    = InputNormalizer::cleanQuantity($this->getCellValue($row, $blockCols['stock_qty'] ?? -1, 0));
                $stockAmt    = $this->parseNumericAmount($this->getCellValue($row, $blockCols['stock_amt'] ?? -1, 0), $currency);

                $poQty       = InputNormalizer::cleanQuantity($this->getCellValue($row, $blockCols['po_qty'] ?? -1, 0));
                $poAmt       = $this->parseNumericAmount($this->getCellValue($row, $blockCols['po_amt'] ?? -1, 0), $currency);

                $forecastQty = InputNormalizer::cleanQuantity($this->getCellValue($row, $blockCols['forecast_qty'] ?? -1, 0));
                $forecastAmt = $this->parseNumericAmount($this->getCellValue($row, $blockCols['forecast_amt'] ?? -1, 0), $currency);

                $deliveryQty = InputNormalizer::cleanQuantity($this->getCellValue($row, $blockCols['delivery_qty'] ?? -1, 0));

                // Rekonsiliasi Formula: Forecast Amount = Forecast Qty * Unit Price
                $calcForecastAmt = round($forecastQty * $unitPrice, 2);
                if ($forecastAmt > 0 && abs($forecastAmt - $calcForecastAmt) > 0.10 && $unitPrice > 0) {
                    $rowWarnings[] = "Selisih amount pada periode {$periodKey}: File={$forecastAmt}, Hitung={$calcForecastAmt}";
                }
                if ($forecastAmt == 0.0 && $forecastQty > 0 && $unitPrice > 0) {
                    $forecastAmt = $calcForecastAmt;
                }

                $totalForecastQty += $forecastQty;
                $totalForecastAmount += $forecastAmt;

                $monthsData[$periodKey] = [
                    'period' => $periodKey,
                    'outstand_qty' => $outstandQty,
                    'outstand_amt' => $outstandAmt,
                    'stock_qty' => $stockQty,
                    'stock_amt' => $stockAmt,
                    'po_qty' => $poQty,
                    'po_amt' => $poAmt,
                    'forecast_qty' => $forecastQty,
                    'forecast_amt' => $forecastAmt,
                    'delivery_qty' => $deliveryQty,
                    'raw_ratio' => $this->getCellValue($row, $blockCols['ratio'] ?? -1, ''),
                ];
            }

            // Kualitas & Status Baris
            $status = 'VALID';
            if ($materialConfidence < 95 || empty($materialCode)) {
                $status = 'REVIEW_REQUIRED';
                $rowWarnings[] = 'Konfidensi Material Code di bawah threshold (' . round($materialConfidence) . '%)';
                $warningCount++;
            } elseif (!empty($rowWarnings)) {
                $status = 'WARNING';
                $warningCount++;
            } else {
                $validCount++;
            }

            $parsedRows[] = [
                'batch_id' => $batchId,
                'source_row_number' => $sourceRowNum,
                'supplier_code' => trim((string)$rawSupplierCode),
                'supplier_name' => $supplierName,
                'plant' => $plant,
                'category_id' => $categoryId,
                'category_code' => $categoryCode,
                'category_name' => $categoryName,
                'raw_category' => trim((string)$rawCategory),
                'material_code' => $materialCode,
                'raw_material_code' => trim((string)$rawMaterialCode),
                'description' => trim((string)$rawDescription),
                'unit_price' => $unitPrice,
                'currency' => $currency,
                'total_forecast_qty' => $totalForecastQty,
                'total_forecast_amount' => $totalForecastAmount,
                'monthly_data' => $monthsData,
                'material_confidence' => round($materialConfidence, 1),
                'supplier_confidence' => round($supplierConfidence, 1),
                'status' => $status,
                'warnings' => $rowWarnings,
            ];
        }

        return [
            'success' => true,
            'batch_id' => $batchId,
            'parsed_rows' => $parsedRows,
            'summary' => [
                'total_rows' => count($parsedRows),
                'valid_rows' => $validCount,
                'warning_rows' => $warningCount,
                'error_rows' => $errorCount,
                'detected_periods' => $detectedPeriods,
            ],
            'periods' => $detectedPeriods,
            'errors' => $errors,
        ];
    }

    /**
     * Deteksi Struktur 3-Level Header
     */
    protected function detectHeaderStructure(array $matrix): array
    {
        $maxScanRows = min(15, count($matrix));
        $columnMap = [
            'supplier_code' => -1,
            'supplier_name' => -1,
            'plant' => -1,
            'category' => -1,
            'material_code' => -1,
            'drawing' => -1,
            'description' => -1,
            'unit_price' => -1,
            'currency' => -1,
        ];
        $monthlyBlocks = [];
        $dataStartRowIdx = 1;

        // Cari baris header utama
        for ($r = 0; $r < $maxScanRows; $r++) {
            $row = $matrix[$r];
            if (!is_array($row)) continue;

            foreach ($row as $colIdx => $val) {
                $str = strtoupper(trim((string)$val));
                
                // Base columns
                if ($columnMap['supplier_code'] === -1 && (str_contains($str, 'SUPPLIER CODE') || str_contains($str, 'VENDOR CODE') || $str === 'VEND_CODE')) {
                    $columnMap['supplier_code'] = $colIdx;
                }
                if ($columnMap['supplier_name'] === -1 && (str_contains($str, 'SUPPLIER NAME') || str_contains($str, 'VENDOR NAME') || $str === 'SUPPLIER' || $str === 'VENDOR')) {
                    $columnMap['supplier_name'] = $colIdx;
                }
                if ($columnMap['plant'] === -1 && (str_contains($str, 'PLANT') || str_contains($str, 'FACTORY') || str_contains($str, 'PABRIK'))) {
                    $columnMap['plant'] = $colIdx;
                }
                if ($columnMap['category'] === -1 && (str_contains($str, 'KATEGORI') || str_contains($str, 'CATEGORY') || $str === 'KAT' || str_contains($str, 'PURCHASING CAT'))) {
                    $columnMap['category'] = $colIdx;
                }
                if ($columnMap['material_code'] === -1 && (str_contains($str, 'MATERIAL CODE') || str_contains($str, 'PART NUMBER') || str_contains($str, 'ITEM CODE') || $str === 'PN' || $str === 'ITEM_CODE' || $str === 'PART_NO')) {
                    $columnMap['material_code'] = $colIdx;
                }
                if ($columnMap['drawing'] === -1 && (str_contains($str, 'DRAWING') || str_contains($str, 'DWG'))) {
                    $columnMap['drawing'] = $colIdx;
                }
                if ($columnMap['description'] === -1 && (str_contains($str, 'DESCRIPTION') || str_contains($str, 'NAMA BARANG') || str_contains($str, 'MATERIAL NAME') || $str === 'DESC')) {
                    $columnMap['description'] = $colIdx;
                }
                if ($columnMap['unit_price'] === -1 && (str_contains($str, 'UNIT PRICE') || str_contains($str, 'PRICE') || str_contains($str, 'HARGA'))) {
                    $columnMap['unit_price'] = $colIdx;
                }
                if ($columnMap['currency'] === -1 && (str_contains($str, 'CURRENCY') || str_contains($str, 'KURS') || str_contains($str, 'MATA UANG') || $str === 'CURR')) {
                    $columnMap['currency'] = $colIdx;
                }

                // Deteksi Bulan Periode Level 1 (Contoh: "JUN-26", "JUL-26", "2026-07", "JULY 2026")
                $periodKey = $this->parsePeriodHeader($str);
                if ($periodKey !== null) {
                    if (!isset($monthlyBlocks[$periodKey])) {
                        $monthlyBlocks[$periodKey] = [
                            'start_col' => $colIdx,
                            'outstand_qty' => -1,
                            'outstand_amt' => -1,
                            'stock_qty' => -1,
                            'stock_amt' => -1,
                            'ratio' => -1,
                            'po_qty' => -1,
                            'po_amt' => -1,
                            'forecast_qty' => -1,
                            'forecast_amt' => -1,
                            'delivery_qty' => -1,
                        ];
                    }
                }
            }

            // Jika base columns sudah terpetakan, baris data dimulai setelah baris header ini atau sub-header di bawahnya
            if ($columnMap['material_code'] !== -1 || $columnMap['description'] !== -1) {
                $dataStartRowIdx = $r + 1;
            }
        }

        // Tentukan data start row secara dinamis (melewati seluruh baris header & sub-header)
        for ($r = 0; $r < $maxScanRows; $r++) {
            $row = $matrix[$r];
            if (!is_array($row)) continue;
            $nonEmptyValues = array_filter($row, fn($v) => trim((string)$v) !== '');
            $hasNumber = false;
            foreach ($nonEmptyValues as $colIdx => $v) {
                // Jangan anggap nama bulan (e.g. JUL-26) sebagai penanda data row
                if ($this->parsePeriodHeader((string)$v) !== null) continue;
                $cleanedNum = str_replace([',', '.'], '', trim((string)$v));
                if (is_numeric($cleanedNum) && strlen($cleanedNum) > 0) {
                    $hasNumber = true;
                    break;
                }
            }
            if ($hasNumber && $r > 0) {
                $dataStartRowIdx = $r;
                break;
            }
        }

        // Cari Level 2 & 3 Sub-Headers pada baris-baris header sebelum baris data ($r < $dataStartRowIdx)
        for ($r = 0; $r < $dataStartRowIdx; $r++) {
            $row = $matrix[$r];
            if (!is_array($row)) continue;

            foreach ($row as $colIdx => $val) {
                $str = strtoupper(trim((string)$val));
                if ($str === '') continue;

                $targetPeriod = $this->findNearestPeriod($colIdx, $monthlyBlocks);
                if ($targetPeriod === null) continue;

                $isAmount = preg_match('/\b(AMOUNT|AMT|VAL|NILAI)\b/i', $str);
                $isQty    = preg_match('/\b(QTY|QUANTITY|UNIT|JML)\b/i', $str);

                if (preg_match('/\b(OUTSTANDING|OUTSTAND)\b/i', $str)) {
                    if ($isAmount) $monthlyBlocks[$targetPeriod]['outstand_amt'] = $colIdx;
                    else $monthlyBlocks[$targetPeriod]['outstand_qty'] = $colIdx;
                } elseif (preg_match('/\b(STOCK|STOK)\b/i', $str)) {
                    if ($isAmount) $monthlyBlocks[$targetPeriod]['stock_amt'] = $colIdx;
                    else $monthlyBlocks[$targetPeriod]['stock_qty'] = $colIdx;
                } elseif (preg_match('/\b(RATIO)\b/i', $str)) {
                    $monthlyBlocks[$targetPeriod]['ratio'] = $colIdx;
                } elseif (preg_match('/\b(PO|PURCHASE[\s_]ORDER|ORDER)\b/i', $str) && !preg_match('/\b(SUPPLIER|VENDOR)\b/i', $str)) {
                    if ($isAmount) $monthlyBlocks[$targetPeriod]['po_amt'] = $colIdx;
                    else $monthlyBlocks[$targetPeriod]['po_qty'] = $colIdx;
                } elseif (preg_match('/\b(FORECAST|FCST)\b/i', $str)) {
                    if ($isAmount) $monthlyBlocks[$targetPeriod]['forecast_amt'] = $colIdx;
                    else $monthlyBlocks[$targetPeriod]['forecast_qty'] = $colIdx;
                } elseif (preg_match('/\b(DELIVERY|INCOMING|RECEIPT)\b/i', $str)) {
                    $monthlyBlocks[$targetPeriod]['delivery_qty'] = $colIdx;
                }
            }
        }

        return [
            'column_map' => $columnMap,
            'monthly_blocks' => $monthlyBlocks,
            'data_start_row_idx' => $dataStartRowIdx,
        ];
    }

    /**
     * Parser Periode Bulan (Contoh: "JUN-26" -> "2026-06", "JUL 2026" -> "2026-07")
     */
    protected function parsePeriodHeader(string $header): ?string
    {
        $clean = strtoupper(trim($header));
        $months = [
            'JAN' => '01', 'FEB' => '02', 'MAR' => '03', 'APR' => '04', 'MAY' => '05', 'JUN' => '06',
            'JUL' => '07', 'JULY' => '07', 'AUG' => '08', 'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DEC' => '12'
        ];

        // Format: "JUL-26" atau "JUL 26"
        if (preg_match('/(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)[A-Z]*[\s\-_]+(20\d{2}|\d{2})/i', $clean, $m)) {
            $monthStr = strtoupper(substr($m[1], 0, 3));
            $monthNum = $months[$monthStr] ?? '01';
            $yearNum = strlen($m[2]) === 2 ? '20' . $m[2] : $m[2];
            return "{$yearNum}-{$monthNum}";
        }

        // Format: "2026-07" atau "2026/07"
        if (preg_match('/(20\d{2})[\/\-_](\d{1,2})/', $clean, $m)) {
            $monthNum = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            return "{$m[1]}-{$monthNum}";
        }

        return null;
    }

    protected function findNearestPeriod(int $colIdx, array $monthlyBlocks): ?string
    {
        $bestPeriod = null;
        $minDist = 999;

        foreach ($monthlyBlocks as $period => $data) {
            $startCol = $data['start_col'] ?? 0;
            if ($colIdx >= $startCol && ($colIdx - $startCol) < 16) {
                $dist = $colIdx - $startCol;
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $bestPeriod = $period;
                }
            }
        }

        return $bestPeriod;
    }

    protected function getCellValue(array $row, int $colIdx, $default = ''): string
    {
        if ($colIdx < 0 || !isset($row[$colIdx])) {
            return (string)$default;
        }
        return trim((string)$row[$colIdx]);
    }

    protected function parseNumericAmount($val, string $currency): float
    {
        if ($val === null || $val === '') return 0.0;
        $num = InputNormalizer::normalizeNumber($val, 4, false);
        return $num !== null ? max(0.0, $num) : 0.0;
    }

    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string)$cell) !== '') return false;
        }
        return true;
    }

    public function isCompanyName(?string $text): bool
    {
        if (empty($text)) return false;
        $clean = strtoupper(trim($text));
        if (preg_match('/^(PT\b|PT\.|CV\b|CV\.|UD\b|UD\.|FA\b|FA\.|TBK\b|INC\b|LTD\b|CORP\b)/i', $clean)) {
            return true;
        }
        $keywords = ['SEJAHTERA', 'INDONESIA', 'NIAGA', 'BIMASAKTI', 'ANEKA', 'SUMBER', 'AGUNG', 'JAYA', 'ABADI', 'SUKSES', 'PERSERO', 'MAKMUR', 'SENTOSA', 'UTAMA', 'KARYA', 'MANDIRI'];
        foreach ($keywords as $kw) {
            if (str_contains($clean, $kw)) return true;
        }
        return false;
    }

    protected function generateBatchId(): string
    {
        return 'FC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
