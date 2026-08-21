<?php

namespace App\Services\DataValidation;

use App\Models\MasterPo;
use App\Models\PurchasingOutstanding;
use App\Models\PurchasingCategory;
use Illuminate\Support\Facades\DB;

/**
 * DataValidator
 * 
 * Centralized Data Validation Layer untuk Purchasing Ecosystem PT Kawai Indonesia.
 * Melakukan validasi menyeluruh: Field types, Master references, Duplicates,
 * Outliers, dan Business Rules.
 */
class DataValidator
{
    /**
     * Cache in-memory untuk referensi master data selama siklus validasi batch
     */
    protected array $masterPartNumbers = [];
    protected array $masterSuppliers = [];
    protected array $knownPoNumbers = [];
    protected array $seenExactRows = [];
    protected array $seenTransactionRows = [];

    public function __construct()
    {
        $this->loadMasterCache();
    }

    /**
     * Muat master data ke in-memory cache untuk performa tinggi saat validasi ribuan baris
     */
    public function loadMasterCache(): void
    {
        // 1. Part Numbers dari PurchasingOutstanding & MasterPo
        try {
            $parts = PurchasingOutstanding::pluck('part_number')->filter()->map(fn($p) => strtoupper(trim($p)))->toArray();
            $drawings = PurchasingOutstanding::pluck('drawing')->filter()->map(fn($d) => strtoupper(trim($d)))->toArray();
            $poParts = MasterPo::pluck('item_code')->filter()->map(fn($p) => strtoupper(trim($p)))->toArray();
            $this->masterPartNumbers = array_unique(array_merge($parts, $drawings, $poParts));
        } catch (\Throwable $e) {
            $this->masterPartNumbers = [];
        }

        // 2. Master Suppliers
        try {
            $suppliers1 = PurchasingOutstanding::pluck('supplier_name')->filter()->map(fn($s) => strtoupper(trim($s)))->toArray();
            $suppliers2 = MasterPo::pluck('supplier')->filter()->map(fn($s) => strtoupper(trim($s)))->toArray();
            $this->masterSuppliers = array_unique(array_merge($suppliers1, $suppliers2));
        } catch (\Throwable $e) {
            $this->masterSuppliers = [];
        }

        // 3. Known PO Numbers
        try {
            $this->knownPoNumbers = MasterPo::pluck('po')->filter()->map(fn($po) => strtoupper(trim($po)))->unique()->toArray();
        } catch (\Throwable $e) {
            $this->knownPoNumbers = [];
        }
    }

    /**
     * Validasi Satu Baris Data (Row-Level Validation)
     * 
     * @param string $templateType (forecast, master_po, incoming, actual_production, actual_inventory, tax_exchange_rate)
     * @param array $rawRow Array asosiatif atau indexed dari satu baris data Excel
     * @param int $rowNumber Nomor baris pada file (1-indexed)
     * @return array [ 'is_valid' => bool, 'status' => string, 'normalized' => array, 'errors' => array, 'warnings' => array, 'suggestions' => array ]
     */
    public function validateRow(string $templateType, array $rawRow, int $rowNumber): array
    {
        $normalized = [];
        $errors = [];
        $warnings = [];
        $suggestions = [];

        switch ($templateType) {
            case 'actual_production':
                $this->validateActualProductionRow($rawRow, $rowNumber, $normalized, $errors, $warnings, $suggestions);
                break;

            case 'actual_inventory':
                $this->validateActualInventoryRow($rawRow, $rowNumber, $normalized, $errors, $warnings, $suggestions);
                break;

            case 'master_po':
                $this->validateMasterPoRow($rawRow, $rowNumber, $normalized, $errors, $warnings, $suggestions);
                break;

            case 'incoming':
                $this->validateIncomingRow($rawRow, $rowNumber, $normalized, $errors, $warnings, $suggestions);
                break;

            case 'forecast':
                $this->validateForecastRow($rawRow, $rowNumber, $normalized, $errors, $warnings, $suggestions);
                break;

            case 'tax_exchange_rate':
                $this->validateExchangeRateRow($rawRow, $rowNumber, $normalized, $errors, $warnings, $suggestions);
                break;

            default:
                $errors[] = "Tipe template '{$templateType}' tidak didukung.";
        }

        // Klasifikasi Status Baris
        $status = 'VALID';
        if (count($errors) > 0) {
            $status = 'INVALID';
        } elseif (count($warnings) > 0) {
            $status = 'WARNING';
        }

        return [
            'row_number' => $rowNumber,
            'is_valid' => count($errors) === 0,
            'status' => $status,
            'normalized' => $normalized,
            'errors' => $errors,
            'warnings' => $warnings,
            'suggestions' => $suggestions,
            'raw' => $rawRow
        ];
    }

    /**
     * Validasi Baris Actual Production
     */
    protected function validateActualProductionRow(array $row, int $rowNumber, array &$normalized, array &$errors, array &$warnings, array &$suggestions): void
    {
        $materialCode = InputNormalizer::normalizeMaterialCode($row['material_code'] ?? $row['item_code'] ?? ($row[3] ?? null));
        $qtyRaw       = $row['production_qty'] ?? $row['actual_production'] ?? $row['qty'] ?? ($row[5] ?? null);
        $qty          = InputNormalizer::normalizeNumber($qtyRaw, 0, true);
        $plant        = InputNormalizer::normalizeFactoryCode($row['plant'] ?? $row['factory_code'] ?? ($row[2] ?? 'Plant 3'));
        $supplierName = InputNormalizer::normalizeSupplierName($row['supplier_name'] ?? $row['supplier'] ?? ($row[1] ?? ''));
        $supplierCode = strtoupper(trim((string) ($row['supplier_code'] ?? ($row[0] ?? ''))));
        $description  = trim((string) ($row['description'] ?? ($row[4] ?? '')));
        $dateRaw      = $row['production_date'] ?? $row['date'] ?? ($row[6] ?? null);
        $date         = InputNormalizer::normalizeDate($dateRaw) ?: date('Y-m-d');

        // 1. Validasi Material Code Wajib
        if ($materialCode === '') {
            $errors[] = "Baris {$rowNumber}: Material Code / Part Number tidak boleh kosong.";
        } else {
            // Master reference check (Warning jika belum terdaftar, jangan silent reject)
            if (!empty($this->masterPartNumbers) && !in_array($materialCode, $this->masterPartNumbers, true)) {
                $warnings[] = "Material Code '{$materialCode}' belum terdaftar pada Master Data. Data baru akan diregistrasikan.";
            }
        }

        // 2. Validasi Production Qty
        if ($qtyRaw === null || trim((string)$qtyRaw) === '') {
            $errors[] = "Baris {$rowNumber}: Production Qty tidak boleh kosong.";
        } elseif ($qty === null) {
            $errors[] = "Baris {$rowNumber}: Production Qty '{$qtyRaw}' bukan angka numerik yang valid.";
            // Deteksi kesalahan OCR seperti '2OO' -> sarankan '200'
            $suggested = preg_replace('/[oO]/', '0', (string)$qtyRaw);
            $suggested = preg_replace('/[iIlL]/', '1', $suggested);
            if (is_numeric($suggested)) {
                $suggestions['production_qty'] = (float) $suggested;
            }
        } elseif ($qty < 0) {
            $errors[] = "Baris {$rowNumber}: Production Qty tidak boleh bernilai negatif ({$qty}).";
        } elseif ($qty > 50000) {
            $warnings[] = "Production Qty sangat tinggi ({$qty} unit). Pastikan angka pemakaian sudah sesuai.";
        }

        // 3. Duplicate Detection (Exact & Transactional)
        $exactHash = md5("{$materialCode}|{$qty}|{$date}|{$plant}|{$supplierCode}");
        if (isset($this->seenExactRows[$exactHash])) {
            $warnings[] = "Baris duplikat persis dengan baris {$this->seenExactRows[$exactHash]}.";
        } else {
            $this->seenExactRows[$exactHash] = $rowNumber;
        }

        $normalized = [
            'material_code'   => $materialCode,
            'production_qty'  => (int) ($qty ?? 0),
            'plant'           => $plant,
            'supplier_code'   => $supplierCode,
            'supplier_name'   => $supplierName,
            'description'     => $description,
            'production_date' => $date,
        ];
    }

    /**
     * Validasi Baris Actual Inventory
     */
    protected function validateActualInventoryRow(array $row, int $rowNumber, array &$normalized, array &$errors, array &$warnings, array &$suggestions): void
    {
        $materialCode = InputNormalizer::normalizeMaterialCode($row['material_code'] ?? $row['item_code'] ?? ($row[3] ?? null));
        $qtyRaw       = $row['actual_inventory'] ?? $row['current_stock'] ?? ($row[5] ?? null);
        $qty          = InputNormalizer::normalizeNumber($qtyRaw, 0, true);
        $plant        = InputNormalizer::normalizeFactoryCode($row['plant'] ?? ($row[2] ?? 'Plant 3'));
        $supplierName = InputNormalizer::normalizeSupplierName($row['supplier_name'] ?? ($row[1] ?? ''));
        $supplierCode = strtoupper(trim((string) ($row['supplier_code'] ?? ($row[0] ?? ''))));
        $description  = trim((string) ($row['description'] ?? ($row[4] ?? '')));
        $dateRaw      = $row['snapshot_date'] ?? ($row[6] ?? null);
        $date         = InputNormalizer::normalizeDate($dateRaw) ?: date('Y-m-d');

        if ($materialCode === '') {
            $errors[] = "Baris {$rowNumber}: Material Code / Part Number tidak boleh kosong.";
        }

        if ($qtyRaw === null || trim((string)$qtyRaw) === '') {
            $errors[] = "Baris {$rowNumber}: Actual Inventory tidak boleh kosong.";
        } elseif ($qty === null) {
            $errors[] = "Baris {$rowNumber}: Nilai stok '{$qtyRaw}' tidak valid numerik.";
        } elseif ($qty < 0) {
            $errors[] = "Baris {$rowNumber}: Stok inventory fisik tidak boleh bernilai negatif ({$qty}).";
        }

        $normalized = [
            'material_code'    => $materialCode,
            'actual_inventory' => (int) ($qty ?? 0),
            'plant'            => $plant,
            'supplier_code'    => $supplierCode,
            'supplier_name'    => $supplierName,
            'description'      => $description,
            'snapshot_date'    => $date,
        ];
    }

    /**
     * Validasi Baris Master PO
     */
    protected function validateMasterPoRow(array $row, int $rowNumber, array &$normalized, array &$errors, array &$warnings, array &$suggestions): void
    {
        $po           = strtoupper(trim((string) ($row['po'] ?? $row['po_number'] ?? ($row[0] ?? ''))));
        $itemCode     = InputNormalizer::normalizeMaterialCode($row['item_code'] ?? $row['part_number'] ?? ($row[1] ?? null));
        $qtyRaw       = $row['qty'] ?? $row['order_qty'] ?? ($row[2] ?? null);
        $qty          = InputNormalizer::normalizeNumber($qtyRaw, 0, true);
        $priceRaw     = $row['price'] ?? $row['unit_price'] ?? ($row[3] ?? 0);
        $price        = InputNormalizer::normalizeNumber($priceRaw, 4, true) ?? 0.0;
        $supplier     = InputNormalizer::normalizeSupplierName($row['supplier'] ?? ($row[4] ?? ''));
        $etaRaw       = $row['eta'] ?? $row['eta_date'] ?? ($row[5] ?? null);
        $eta          = InputNormalizer::normalizeDate($etaRaw);
        $currency     = InputNormalizer::normalizeCurrency($row['currency'] ?? 'USD');
        $factoryCode  = InputNormalizer::normalizeFactoryCode($row['factory_code'] ?? 'Plant 3');

        if ($po === '') {
            $errors[] = "Baris {$rowNumber}: Nomor PO resmi wajib diisi.";
        }

        if ($itemCode === '') {
            $errors[] = "Baris {$rowNumber}: Item Code / Part Number wajib diisi.";
        }

        if ($qtyRaw === null || $qty === null || $qty <= 0) {
            $errors[] = "Baris {$rowNumber}: Order Qty harus bernilai angka positif (> 0).";
        }

        if ($price < 0) {
            $errors[] = "Baris {$rowNumber}: Harga satuan tidak boleh negatif.";
        }

        $normalized = [
            'po'           => $po,
            'item_code'    => $itemCode,
            'qty'          => (int) ($qty ?? 0),
            'price'        => $price,
            'supplier'     => $supplier,
            'eta'          => $eta,
            'currency'     => $currency,
            'factory_code' => $factoryCode,
        ];
    }

    /**
     * Validasi Baris Incoming Penerimaan PO
     */
    protected function validateIncomingRow(array $row, int $rowNumber, array &$normalized, array &$errors, array &$warnings, array &$suggestions): void
    {
        $poRef       = strtoupper(trim((string) ($row['po_reference'] ?? $row['po'] ?? '')));
        $itemCode    = InputNormalizer::normalizeMaterialCode($row['item_code'] ?? $row['part_number'] ?? null);
        $receivedRaw = $row['actual_received'] ?? $row['qty_received'] ?? null;
        $received    = InputNormalizer::normalizeNumber($receivedRaw, 0, true);
        $supplier    = InputNormalizer::normalizeSupplierName($row['supplier'] ?? '');
        $date        = InputNormalizer::normalizeDate($row['date'] ?? null) ?: date('Y-m-d');
        $iadStatus   = strtoupper(trim((string) ($row['iad_status'] ?? 'PASS')));
        $price       = InputNormalizer::normalizeNumber($row['price'] ?? 0, 4, true) ?? 0.0;

        if ($itemCode === '') {
            $errors[] = "Baris {$rowNumber}: Item Code / Part Number wajib diisi.";
        }

        if ($receivedRaw === null || $received === null || $received < 0) {
            $errors[] = "Baris {$rowNumber}: Kuantitas diterima (Actual Received) harus angka non-negatif (&ge; 0).";
        }

        // Cross-check dengan Master PO jika po_reference diisi
        if ($poRef !== '' && !empty($this->knownPoNumbers) && !in_array($poRef, $this->knownPoNumbers, true)) {
            $warnings[] = "Nomor PO '{$poRef}' belum terdaftar di Master PO. Pastikan PO telah diterbitkan.";
        }

        $normalized = [
            'po_reference'    => $poRef,
            'item_code'       => $itemCode,
            'actual_received' => (int) ($received ?? 0),
            'supplier'        => $supplier,
            'date'            => $date,
            'iad_status'      => $iadStatus,
            'price'           => $price,
        ];
    }

    /**
     * Validasi Baris Forecast Master
     */
    protected function validateForecastRow(array $row, int $rowNumber, array &$normalized, array &$errors, array &$warnings, array &$suggestions): void
    {
        $materialCode = InputNormalizer::normalizeMaterialCode($row['material_code'] ?? $row['part_number'] ?? null);
        $targetQty    = InputNormalizer::normalizeNumber($row['target_qty'] ?? $row['target_order'] ?? 0, 0, true) ?? 0;
        $supplier     = InputNormalizer::normalizeSupplierName($row['supplier'] ?? '');
        $price        = InputNormalizer::normalizeNumber($row['price'] ?? 0, 4, true) ?? 0.0;
        $currency     = InputNormalizer::normalizeCurrency($row['currency'] ?? 'USD');
        $factoryCode  = InputNormalizer::normalizeFactoryCode($row['factory_code'] ?? 'Plant 3');

        if ($materialCode === '') {
            $errors[] = "Baris {$rowNumber}: Material Code / Part Number tidak boleh kosong.";
        }

        $normalized = [
            'material_code' => $materialCode,
            'target_qty'    => (int) $targetQty,
            'supplier'      => $supplier,
            'price'         => $price,
            'currency'      => $currency,
            'factory_code'  => $factoryCode,
        ];
    }

    /**
     * Validasi Baris Kurs Pajak KMK
     */
    protected function validateExchangeRateRow(array $row, int $rowNumber, array &$normalized, array &$errors, array &$warnings, array &$suggestions): void
    {
        $currency  = InputNormalizer::normalizeCurrency($row['currency'] ?? 'USD');
        $rateRaw   = $row['rate'] ?? $row['nilai_kurs'] ?? null;
        $rate      = InputNormalizer::normalizeNumber($rateRaw, 4, true);
        $startDate = InputNormalizer::normalizeDate($row['start_date'] ?? null);
        $endDate   = InputNormalizer::normalizeDate($row['end_date'] ?? null);
        $kmkNumber = trim((string) ($row['kmk_number'] ?? ''));

        if ($rate === null || $rate <= 0) {
            $errors[] = "Baris {$rowNumber}: Nilai kurs harus berupa angka positif (> 0).";
        }

        if (!$startDate || !$endDate) {
            $errors[] = "Baris {$rowNumber}: Tanggal berlaku awal dan akhir wajib diisi dalam format tanggal yang valid.";
        } elseif ($startDate > $endDate) {
            $errors[] = "Baris {$rowNumber}: Tanggal mulai ({$startDate}) tidak boleh lebih besar dari tanggal akhir ({$endDate}).";
        }

        $normalized = [
            'currency'   => $currency,
            'rate'       => $rate ?? 0.0,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'kmk_number' => $kmkNumber,
        ];
    }

    /**
     * Validasi Keseluruhan Dataset Batch (Batch Validation)
     */
    public function validateBatch(string $templateType, array $rows): array
    {
        $this->seenExactRows = [];
        $this->seenTransactionRows = [];

        $totalRows = count($rows);
        $validRows = [];
        $warningRows = [];
        $invalidRows = [];
        $allErrors = [];
        $totalQty = 0;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 1;
            $res = $this->validateRow($templateType, $row, $rowNumber);

            if ($res['is_valid']) {
                if (count($res['warnings']) > 0) {
                    $warningRows[] = $res;
                } else {
                    $validRows[] = $res;
                }

                // Akumulasi Qty untuk Rekonsiliasi
                $qtyField = $res['normalized']['production_qty'] ?? $res['normalized']['actual_inventory'] ?? $res['normalized']['qty'] ?? $res['normalized']['actual_received'] ?? $res['normalized']['target_qty'] ?? 0;
                $totalQty += (float) $qtyField;
            } else {
                $invalidRows[] = $res;
                $allErrors = array_merge($allErrors, $res['errors']);
            }
        }

        $validCount = count($validRows) + count($warningRows);
        $invalidCount = count($invalidRows);
        $warningCount = count($warningRows);

        return [
            'template_type'   => $templateType,
            'total_rows'      => $totalRows,
            'valid_count'     => $validCount,
            'warning_count'   => $warningCount,
            'invalid_count'   => $invalidCount,
            'is_fully_valid'  => $invalidCount === 0,
            'valid_rows'      => array_merge($validRows, $warningRows),
            'invalid_rows'    => $invalidRows,
            'warning_rows'    => $warningRows,
            'all_errors'      => $allErrors,
            'total_qty_sum'   => $totalQty,
            'quality_score'   => $totalRows > 0 ? round(($validCount / $totalRows) * 100, 1) : 100.0,
        ];
    }
}
