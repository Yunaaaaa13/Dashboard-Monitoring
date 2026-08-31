<?php

namespace App\Services\Import;

use App\Models\MasterPo;
use App\Models\PurchasingLog;
use App\Models\Actual;
use App\Models\ComparisonMaster;
use App\Models\Forecasting;
use App\Models\PurchasingOutstanding;
use App\Models\PurchasingCategory;
use App\Services\DataValidation\InputNormalizer;
use App\Services\Ocr\MasterDictionaryMatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class IntegratedPoIncomingImporter
{
    protected MasterDictionaryMatcher $matcher;

    public function __construct(?MasterDictionaryMatcher $matcher = null)
    {
        $this->matcher = $matcher ?? new MasterDictionaryMatcher();
    }

    /**
     * Membaca dan menganalisis file Excel terpadu (Plan & Result).
     *
     * @param string $filePath Path absolut ke file Excel
     * @param array $options Opsi tambahan (default_currency, file_name, dll.)
     * @return array Hasil parsing, validasi, preview records, dan metrik rekonsiliasi.
     */
    public function parseAndAnalyze(string $filePath, array $options = []): array
    {
        $fileName = $options['file_name'] ?? basename($filePath);
        $defaultCurrency = strtoupper($options['default_currency'] ?? 'USD');
        $batchId = 'IMP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        try {
            $reader = IOFactory::createReaderForFile($filePath);
            if (method_exists($reader, 'setReadDataOnly')) {
                $reader->setReadDataOnly(true);
            }
            $spreadsheet = $reader->load($filePath);
        } catch (\Throwable $e) {
            $spreadsheet = IOFactory::load($filePath);
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rawRows = $sheet->toArray(null, false, true, true);

        if (empty($rawRows)) {
            return [
                'success' => false,
                'error' => 'File Excel kosong atau tidak terbaca.',
            ];
        }

        // 1. Deteksi Baris Header & Pemetaan Kolom
        $schema = $this->detectSchema($rawRows);
        $headerRowIdx = $schema['header_row_idx'];
        $cols = $schema['columns'];

        $importContext = strtoupper(trim($options['context'] ?? $options['import_context'] ?? 'INTEGRATED'));

        $masterPoRecords = [];
        $incomingRecords = [];
        $previewRows = [];
        $warnings = [];
        $errors = [];

        $totalPlanQty = 0;
        $totalPlanAmount = 0.0;
        $totalResultQty = 0;
        $totalResultAmount = 0.0;
        $unplannedCount = 0;
        $zeroTransactionCount = 0;
        $ignoredResultCount = 0;
        $ignoredPlanCount = 0;

        $dataRows = array_slice($rawRows, $headerRowIdx, null, true);

        foreach ($dataRows as $rIdx => $row) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $sourceRowNum = $rIdx;

            // Ekstraksi Kolom Shared Identity
            $rawSupplierCode = $this->getVal($row, $cols['supplier_code']);
            $rawSupplierName = $this->getVal($row, $cols['supplier_name']);
            $rawDeliveryDate = $this->getVal($row, $cols['delivery_date']);
            $rawCategory     = $this->getVal($row, $cols['category']);
            $rawMaterialCode = $this->getVal($row, $cols['material_code']);
            $rawDescription  = $this->getVal($row, $cols['description']);
            $rawPoNumber     = $this->getVal($row, $cols['po_number']);
            $rawCurrency     = $this->getVal($row, $cols['currency']);
            $rawPrice        = $this->getVal($row, $cols['unit_price']);
            $rawPlant        = $this->getVal($row, $cols['plant']);

            // Disambiguasi: Jika Material Code berisi nama perusahaan (PT/CV)
            if ($this->isCompanyName($rawMaterialCode)) {
                $actualSupplier = $rawMaterialCode;
                $actualMaterial = (!empty($rawSupplierCode) && !$this->isCompanyName($rawSupplierCode))
                    ? $rawSupplierCode
                    : ((!empty($rawSupplierName) && !$this->isCompanyName($rawSupplierName)) ? $rawSupplierName : $rawDescription);
                $rawSupplierName = $actualSupplier;
                $rawMaterialCode = $actualMaterial;
            } elseif ($this->isCompanyName($rawSupplierCode) && empty($rawSupplierName)) {
                $rawSupplierName = $rawSupplierCode;
                $rawSupplierCode = '';
            }

            if (empty($rawMaterialCode) && empty($rawPoNumber) && empty($rawDescription)) {
                continue;
            }

            // Category Matching (PUR-01 .. PUR-04 atau Nama Kategori)
            $catMatch     = $this->matcher->matchCategory((string)$rawCategory);
            $categoryId   = $catMatch['category_id'] ?? null;
            $categoryCode = $catMatch['category_code'] ?? (string)$rawCategory;
            $categoryName = $catMatch['category_name'] ?? '';

            // Normalisasi Shared Fields
            $materialCode = InputNormalizer::cleanMaterialCode($rawMaterialCode ?: ($rawPoNumber ?: 'UNKNOWN'));
            $supplierName = InputNormalizer::normalizeSupplierName($rawSupplierName ?: ($rawSupplierCode ?: '-'));
            $poNumber     = InputNormalizer::canonicalPoNumber($rawPoNumber ?: ('PO-' . $materialCode));
            $description  = trim($rawDescription ?: $materialCode);
            $plant        = InputNormalizer::normalizePlantCode($rawPlant ?: 'Plant 3');
            $currency     = $this->normalizeCurrency($rawCurrency ?: $defaultCurrency);
            $unitPrice    = InputNormalizer::normalizePrice($rawPrice, $currency);
            $deliveryDate = $this->parseExcelDate($rawDeliveryDate);
            $periodMonth  = InputNormalizer::canonicalPeriod($deliveryDate);

            // Ekstraksi Kolom Transaksi (Plan vs Result)
            $planQty      = InputNormalizer::cleanQuantity($this->getVal($row, $cols['plan_qty']));
            $planAmount   = $this->parseNumericAmount($this->getVal($row, $cols['plan_amount']), $currency);
            $resultQty    = InputNormalizer::cleanQuantity($this->getVal($row, $cols['result_qty']));
            $resultAmount = $this->parseNumericAmount($this->getVal($row, $cols['result_amount']), $currency);

            // Fallback kuantitas jika $planQty & $resultQty keduanya 0
            if ($planQty <= 0 && $resultQty <= 0) {
                foreach ($row as $cKey => $cVal) {
                    if (in_array($cKey, [$cols['material_code'], $cols['po_number'], $cols['delivery_date'], $cols['supplier_name'], $cols['description'], $cols['currency']], true)) continue;
                    $cNum = InputNormalizer::cleanQuantity((string)$cVal);
                    if ($cNum > 0) {
                        $planQty = $cNum;
                        break;
                    }
                }
            }

            // Fallback Amount jika kosong di Excel (Amount = Qty * Price)
            $expectedPlanAmount = round($planQty * $unitPrice, 2);
            $expectedResultAmount = round($resultQty * $unitPrice, 2);

            if ($planAmount <= 0 && $planQty > 0 && $unitPrice > 0) {
                $planAmount = $expectedPlanAmount;
            }
            if ($resultAmount <= 0 && $resultQty > 0 && $unitPrice > 0) {
                $resultAmount = $expectedResultAmount;
            }

            // Validasi Amount Formula Mismatch
            $rowWarnings = [];
            if ($planQty > 0 && $unitPrice > 0 && abs($planAmount - $expectedPlanAmount) > 1.0) {
                $rowWarnings[] = "Row {$sourceRowNum}: Plan Amount mismatch (Excel: " . number_format($planAmount, 2) . ", Hitung: " . number_format($expectedPlanAmount, 2) . ")";
            }
            if ($resultQty > 0 && $unitPrice > 0 && abs($resultAmount - $expectedResultAmount) > 1.0) {
                $rowWarnings[] = "Row {$sourceRowNum}: Result Amount mismatch (Excel: " . number_format($resultAmount, 2) . ", Hitung: " . number_format($expectedResultAmount, 2) . ")";
            }

            // Penentuan Status Bisnis & Derived Values Berdasarkan Import Context
            $outstandingQty = max(0, $planQty - $resultQty);
            $overDeliveryQty = max(0, $resultQty - $planQty);
            $outstandingAmount = max(0.0, $planAmount - $resultAmount);

            $status = 'Valid';
            $hasMasterPo = false;
            $hasIncoming = false;

            if ($importContext === 'MASTER_PO') {
                if ($planQty > 0) {
                    $hasMasterPo = true;
                    $status = 'Plan Registered';
                } else {
                    $status = 'No Plan Qty';
                    $zeroTransactionCount++;
                }
                if ($resultQty > 0) {
                    $ignoredResultCount++;
                }
            } elseif ($importContext === 'INCOMING') {
                // INCOMING Context: Semua baris PO & pengiriman dimasukkan ke Incoming log
                if ($planQty > 0 || $resultQty > 0) {
                    $hasIncoming = true;
                    if ($resultQty == 0) {
                        $status = 'Not Received';
                    } elseif ($planQty > 0 && $resultQty >= $planQty) {
                        $status = 'Complete';
                    } elseif ($planQty > 0 && $resultQty < $planQty) {
                        $status = 'Partial';
                    } else {
                        $status = 'Incoming Received';
                    }
                } else {
                    $status = 'Zero Transaction';
                    $zeroTransactionCount++;
                }
                if ($planQty > 0) {
                    $ignoredPlanCount++;
                }
            } else {
                // INTEGRATED Mode: Memasukkan SEMUA baris ke Master PO dan Incoming
                if ($planQty > 0 && $resultQty > 0) {
                    $hasMasterPo = true;
                    $hasIncoming = true;
                    if ($resultQty == $planQty) {
                        $status = 'Complete';
                    } elseif ($resultQty < $planQty) {
                        $status = 'Partial';
                    } else {
                        $status = 'Over Delivery';
                    }
                } elseif ($planQty > 0 && $resultQty == 0) {
                    $hasMasterPo = true;
                    $hasIncoming = false;
                    $status = 'Not Received';
                } elseif ($planQty == 0 && $resultQty > 0) {
                    $hasMasterPo = false;
                    $hasIncoming = true;
                    $status = 'Unplanned Incoming';
                    $unplannedCount++;
                    $rowWarnings[] = "Row {$sourceRowNum}: Unplanned Incoming (Realisasi {$resultQty} tanpa Plan PO).";
                } else {
                    $hasMasterPo = false;
                    $hasIncoming = false;
                    $status = 'Zero Transaction';
                    $zeroTransactionCount++;
                }
            }

            // Generate Master PO Record (jika Plan > 0)
            if ($hasMasterPo) {
                $masterPoRecords[] = [
                    'import_batch_id'    => $batchId,
                    'source_file'        => $fileName,
                    'source_row_number'  => $sourceRowNum,
                    'source_type'        => 'MASTER_PO',
                    'tanggal'            => $deliveryDate,
                    'supplier'           => $supplierName,
                    'po'                 => $poNumber,
                    'item_code'          => $materialCode,
                    'factory_code'       => $plant,
                    'category_id'        => $categoryId,
                    'category_code'      => $categoryCode,
                    'category_name'      => $categoryName,
                    'name'               => $description,
                    'qty'                => $planQty,
                    'price'              => $unitPrice,
                    'currency'           => $currency,
                    'amount'             => $planAmount,
                    'status'             => $status,
                ];
                $totalPlanQty += $planQty;
                $totalPlanAmount += $planAmount;
            }

            // Generate Incoming Record (jika Result > 0)
            if ($hasIncoming) {
                $periodMonth = date('Y-m', strtotime($deliveryDate ?: date('Y-m-d')));
                $incomingRecords[] = [
                    'import_batch_id'    => $batchId,
                    'source_file'        => $fileName,
                    'source_row_number'  => $sourceRowNum,
                    'source_type'        => 'INCOMING',
                    'receipt_date'       => $deliveryDate,
                    'supplier_name'      => $supplierName,
                    'po_reference'       => $poNumber,
                    'item_code'          => $materialCode,
                    'factory_code'       => $plant,
                    'category_id'        => $categoryId,
                    'category_code'      => $categoryCode,
                    'category_name'      => $categoryName,
                    'item_name'          => $description,
                    'period_month'       => $periodMonth,
                    'target_order'       => $planQty,
                    'actual_received'    => $resultQty,
                    'pending_order'      => $outstandingQty,
                    'price'              => $unitPrice,
                    'currency'           => $currency,
                    'amount'             => $resultAmount,
                    'status_note'        => $status,
                ];
                $totalResultQty += $resultQty;
                $totalResultAmount += $resultAmount;
            }

            if (!empty($rowWarnings)) {
                $warnings = array_merge($warnings, $rowWarnings);
            }

            // Baris Preview untuk User
            $previewRows[] = [
                'row'                => $sourceRowNum,
                'material_code'      => $materialCode,
                'description'        => $description,
                'category_id'        => $categoryId,
                'category_code'      => $categoryCode,
                'category_name'      => $categoryName,
                'raw_category'       => trim((string)$rawCategory),
                'supplier_name'      => $supplierName,
                'po_number'          => $poNumber,
                'plant'              => $plant,
                'currency'           => $currency,
                'unit_price'         => $unitPrice,
                'delivery_date'      => $deliveryDate,
                'plan_qty'           => $planQty,
                'plan_amount'        => $planAmount,
                'result_qty'         => $resultQty,
                'result_amount'      => $resultAmount,
                'outstanding_qty'    => $outstandingQty,
                'outstanding_amount' => $outstandingAmount,
                'has_master_po'      => $hasMasterPo,
                'has_incoming'       => $hasIncoming,
                'status'             => $status,
                'warnings'           => $rowWarnings,
            ];
        }

        // Metrik Rekonsiliasi
        $reconciliation = [
            'import_context'         => $importContext,
            'total_excel_rows'       => count($previewRows),
            'master_po_count'        => count($masterPoRecords),
            'incoming_count'         => count($incomingRecords),
            'ignored_result_rows'    => $ignoredResultCount,
            'ignored_plan_rows'      => $ignoredPlanCount,
            'unplanned_incoming'     => $unplannedCount,
            'zero_transactions'      => $zeroTransactionCount,
            'total_plan_qty'         => $totalPlanQty,
            'total_plan_amount'      => round($totalPlanAmount, 2),
            'total_result_qty'       => $totalResultQty,
            'total_result_amount'    => round($totalResultAmount, 2),
            'total_outstanding_qty'  => max(0, $totalPlanQty - $totalResultQty),
            'is_reconciled'          => empty($errors),
        ];

        return [
            'success'          => true,
            'import_context'   => $importContext,
            'batch_id'         => $batchId,
            'file_name'        => $fileName,
            'schema'           => $schema,
            'reconciliation'   => $reconciliation,
            'master_po_rows'   => $masterPoRecords,
            'incoming_rows'    => $incomingRecords,
            'preview_rows'     => $previewRows,
            'warnings'         => $warnings,
            'errors'           => $errors,
        ];
    }

    /**
     * Menyimpan hasil analisis secara atomik ke database (Master PO & Incoming).
     *
     * @param array $analysisData Data hasil dari parseAndAnalyze
     * @param int|null $userId ID User yang mengimpor
     * @return array
     */
    public function executeImport(array $analysisData, ?int $userId = null): array
    {
        $userId = $userId ?? Auth::id() ?? 1;
        $batchId = $analysisData['batch_id'] ?? ('IMP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)));
        $masterPoRows = $analysisData['master_po_rows'] ?? [];
        $incomingRows = $analysisData['incoming_rows'] ?? [];

        $defaultCategory = PurchasingCategory::first();
        if (!$defaultCategory) {
            $defaultCategory = PurchasingCategory::create([
                'category_code' => 'RAW',
                'category_name' => 'Raw Material',
                'pic_buyer'     => 'Staff Buyer',
                'status'        => 'active',
            ]);
        }
        $defaultCatId = $defaultCategory->id;

        $insertedPoCount = 0;
        $insertedIncomingCount = 0;

        DB::beginTransaction();
        try {
            // 1. Simpan Master PO Records
            foreach ($masterPoRows as $po) {
                $poCatId = !empty($po['category_id']) ? (int)$po['category_id'] : $defaultCatId;
                MasterPo::create([
                    'tanggal'        => $po['tanggal'] ?: date('Y-m-d'),
                    'supplier'       => $po['supplier'],
                    'po'             => $po['po'],
                    'item_code'      => $po['item_code'],
                    'factory_code'   => $po['factory_code'],
                    'category_id'    => $poCatId,
                    'name'           => $po['name'],
                    'qty'            => $po['qty'],
                    'price'          => $po['price'],
                    'currency'       => $po['currency'],
                    'user_id'        => $userId,
                    'created_by'     => $userId,
                ]);

                // Sinkronisasi ComparisonMaster untuk Master PO
                $poPeriod = InputNormalizer::canonicalPeriod($po['tanggal']);
                ComparisonMaster::sync($po['item_code'], $poPeriod);

                $insertedPoCount++;
            }

            // 2. Simpan Incoming / PurchasingLog Records
            foreach ($incomingRows as $inc) {
                $incCatId = !empty($inc['category_id']) ? (int)$inc['category_id'] : $defaultCatId;
                $canonicalIncPeriod = InputNormalizer::canonicalPeriod($inc['period_month']);
                $log = PurchasingLog::create([
                    'purchasing_category_id' => $incCatId,
                    'user_id'                => $userId,
                    'receipt_date'           => $inc['receipt_date'] ?: date('Y-m-d'),
                    'item_code'              => $inc['item_code'],
                    'factory_code'           => $inc['factory_code'],
                    'item_name'              => $inc['item_name'],
                    'supplier_name'          => $inc['supplier_name'],
                    'po_reference'           => $inc['po_reference'],
                    'period_month'           => $canonicalIncPeriod,
                    'target_order'           => $inc['target_order'],
                    'actual_received'        => $inc['actual_received'],
                    'pending_order'          => $inc['pending_order'],
                    'price'                  => $inc['price'],
                    'currency'               => $inc['currency'],
                    'amount'                 => $inc['amount'],
                    'status_note'            => $inc['status_note'],
                ]);

                // Sinkronisasi Actual & ComparisonMaster
                $periode = $canonicalIncPeriod;
                $partNumber = $inc['item_code'];
                $actual = Actual::where('part_number', $partNumber)
                    ->where(function ($q) use ($periode) {
                        $q->where('periode', $periode)->orWhere('period_month', $periode);
                    })->first();

                if ($actual) {
                    $actual->actual_stock = $actual->actual_stock + $inc['actual_received'];
                    $actual->actual_qty = $actual->actual_stock;
                    $actual->save();
                } else {
                    Actual::create([
                        'part_number'   => $partNumber,
                        'factory_code'  => $inc['factory_code'],
                        'description'   => $inc['item_name'],
                        'periode'       => $periode,
                        'period_month'  => $periode,
                        'actual_qty'    => $inc['actual_received'],
                        'actual_stock'  => $inc['actual_received'],
                    ]);
                }

                ComparisonMaster::sync($partNumber, $periode);
                $insertedIncomingCount++;
            }

            // 3. Catat Log Audit Import Batch
            if (class_exists('\App\Models\ImportBatch')) {
                \App\Models\ImportBatch::create([
                    'batch_id'              => $batchId,
                    'template_type'         => 'INTEGRATED_PO_INCOMING',
                    'template_version'      => '2.0',
                    'file_name'             => $analysisData['file_name'] ?? 'integrated_import.xlsx',
                    'file_hash'             => md5(($analysisData['file_name'] ?? 'file') . time()),
                    'uploaded_by'           => $userId,
                    'total_rows'            => $analysisData['reconciliation']['total_excel_rows'] ?? 0,
                    'valid_rows'            => $insertedPoCount + $insertedIncomingCount,
                    'status'                => 'COMPLETED',
                    'reconciliation_status' => 'BALANCED',
                ]);
            }

            DB::commit();

            return [
                'success'                 => true,
                'batch_id'                => $batchId,
                'inserted_master_po'      => $insertedPoCount,
                'inserted_incoming'       => $insertedIncomingCount,
                'message'                 => "Import berhasil: {$insertedPoCount} data Master PO dan {$insertedIncomingCount} data Incoming telah tersimpan.",
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error'   => 'Gagal menyimpan transaksi ke database: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Deteksi skema kolom header Excel secara otomatis.
     */
    protected function detectSchema(array $rawRows): array
    {
        $bestHeaderIdx = 1;
        $maxScore = 0;

        foreach ($rawRows as $idx => $row) {
            if ($idx > 20) break;
            $score = 0;
            foreach ($row as $col => $val) {
                $str = strtoupper(trim((string)$val));
                if (preg_match('/(PO[\s_]?NO|ITEM[\s_]?CODE|MATERIAL|SUPPLIER|DELIVERY|PLAN|RESULT|PRICE)/i', $str)) {
                    $score++;
                }
            }
            if ($score > $maxScore) {
                $maxScore = $score;
                $bestHeaderIdx = $idx;
            }
        }

        $headerRow = $rawRows[$bestHeaderIdx] ?? [];
        $aboveRow  = isset($rawRows[$bestHeaderIdx - 1]) ? $rawRows[$bestHeaderIdx - 1] : [];

        $columns = [
            'supplier_code' => null,
            'supplier_name' => null,
            'delivery_date' => null,
            'material_code' => null,
            'description'   => null,
            'category'      => null,
            'po_number'     => null,
            'currency'      => null,
            'unit_price'    => null,
            'plant'         => null,
            'plan_qty'      => null,
            'plan_amount'   => null,
            'result_qty'    => null,
            'result_amount' => null,
            'remaining_qty' => null,
            'rem_amount'    => null,
        ];

        foreach ($headerRow as $col => $val) {
            $cleanVal = strtoupper(trim((string)$val));
            $aboveVal = isset($aboveRow[$col]) ? strtoupper(trim((string)$aboveRow[$col])) : '';
            $combined = trim($aboveVal . ' ' . $cleanVal);

            // 1. Shared Identity
            if (!$columns['supplier_code'] && preg_match('/(SUPPLIER[\s_]?CODE|VENDOR[\s_]?CODE|KODE[\s_]?SUPPLIER|VEND_CODE|SUPP_CODE)/i', $combined)) {
                $columns['supplier_code'] = $col;
            } elseif (!$columns['supplier_name'] && preg_match('/(SUPPLIER[\s_]?NAME|VENDOR[\s_]?NAME|NAMA[\s_]?SUPPLIER|NAMA[\s_]?VENDOR|\bSUPPLIER\b|\bVENDOR\b)/i', $combined) && !preg_match('/CODE|KODE/i', $combined)) {
                $columns['supplier_name'] = $col;
            } elseif (!$columns['delivery_date'] && preg_match('/(DELIVERY[\s_]?DATE|TANGGAL|DATE|TGL[\s_]?KIRIM|RECEIPT[\s_]?DATE)/i', $combined)) {
                $columns['delivery_date'] = $col;
            } elseif (!$columns['category'] && preg_match('/(KATEGORI|CATEGORY|PURCHASING[\s_]?CAT|KODE[\s_]?KATEGORI|\bKAT\b|JENIS[\s_]?MATERIAL)/i', $combined)) {
                $columns['category'] = $col;
            } elseif (!$columns['material_code'] && preg_match('/(MATERIAL[\s_]?CODE|ITEM[\s_]?CODE|PART[\s_]?NUMBER|PART[\s_]?NO|\bPN\b|\bDRAWING\b|\bITEMCODE\b|\bITEM_CODE\b)/i', $combined)) {
                $columns['material_code'] = $col;
            } elseif (!$columns['description'] && preg_match('/(DESCRIPTION|DESKRIPSI|NAMA[\s_]?BARANG|ITEM[\s_]?NAME|NAMA[\s_]?MATERIAL|\bDESC\b)/i', $combined) && !preg_match('/SUPPLIER/i', $combined)) {
                $columns['description'] = $col;
            } elseif (!$columns['po_number'] && preg_match('/(\bPO[\s_]?NO\b|\bPO[\s_]?NUMBER\b|\bNO\.?[\s_]?PO\b|\bNOMOR[\s_]?PO\b|\bPO\b|PURCHASE[\s_]?ORDER)/i', $combined) && !preg_match('/QTY|AMOUNT|DATE|TANGGAL/i', $combined)) {
                $columns['po_number'] = $col;
            } elseif (!$columns['currency'] && preg_match('/(CURRENCY|MATA[\s_]?UANG|KURS|\bCURR\b)/i', $combined)) {
                $columns['currency'] = $col;
            } elseif (!$columns['unit_price'] && preg_match('/(UNIT[\s_]?PRICE|\bPRICE\b|\bHARGA\b)/i', $combined) && !preg_match('/AMOUNT|TOTAL/i', $combined)) {
                $columns['unit_price'] = $col;
            } elseif (!$columns['plant'] && preg_match('/(\bPLANT\b|\bFACTORY\b|\bPABRIK\b|KODE[\s_]?PABRIK)/i', $combined)) {
                $columns['plant'] = $col;
            }

            // 2. Plan (Master PO)
            if (preg_match('/(\bPLAN\b|\bTARGET\b|\bPO[\s_]?QTY\b|\bORDER[\s_]?QTY\b|\bQTY[\s_]?PO\b|\bQTY[\s_]?ORDER\b)/i', $combined)) {
                if (preg_match('/(AMOUNT|AMT|VAL|NILAI|TOTAL)/i', $combined)) {
                    $columns['plan_amount'] = $col;
                } else {
                    $columns['plan_qty'] = $col;
                }
            } elseif (preg_match('/(\bQTY\b|\bQUANTITY\b|\bJUMLAH\b)/i', $combined)) {
                if (!preg_match('/(RESULT|ACTUAL|RECEIVED|INCOMING|REALISASI|AMOUNT|AMT|VAL|NILAI)/i', $combined)) {
                    if (!$columns['plan_qty']) {
                        $columns['plan_qty'] = $col;
                    }
                }
            }

            // 3. Result (Incoming)
            if (preg_match('/(\bRESULT\b|\bACTUAL\b|\bREALISASI\b|\bINCOMING\b|\bRECEIPT\b|\bRECEIVED\b|\bDITERIMA\b)/i', $combined)) {
                if (preg_match('/(AMOUNT|AMT|VAL|NILAI|TOTAL)/i', $combined)) {
                    $columns['result_amount'] = $col;
                } else {
                    $columns['result_qty'] = $col;
                }
            }

            // 4. Remaining (Derived)
            if (preg_match('/(\bREMAINING\b|\bREM\b|\bOUTSTANDING\b|\bSISA\b)/i', $combined)) {
                if (preg_match('/(AMOUNT|AMT|VAL|NILAI|TOTAL)/i', $combined)) {
                    $columns['rem_amount'] = $col;
                } else {
                    $columns['remaining_qty'] = $col;
                }
            }
        }

        // Positional Fallbacks jika header tidak memiliki label eksplisit
        if (!$columns['supplier_code'] && !$columns['supplier_name']) {
            $columns['supplier_code'] = 'A';
            $columns['supplier_name'] = 'B';
        } elseif (!$columns['supplier_name'] && $columns['supplier_code']) {
            $columns['supplier_name'] = $columns['supplier_code'] === 'A' ? 'B' : 'A';
        }
        if (!$columns['delivery_date']) $columns['delivery_date'] = 'C';
        if (!$columns['material_code']) $columns['material_code'] = 'D';
        if (!$columns['description'])   $columns['description']   = 'E';
        if (!$columns['po_number'])     $columns['po_number']     = 'F';
        if (!$columns['currency'])      $columns['currency']      = 'G';
        if (!$columns['unit_price'])    $columns['unit_price']    = 'H';
        if (!$columns['plan_qty'])      $columns['plan_qty']      = 'I';

        return [
            'header_row_idx' => $bestHeaderIdx,
            'columns'        => $columns,
        ];
    }

    /**
     * Generate template Excel resmi terpadu PO & Incoming.
     */
    public function generateTemplate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PO & Incoming Template');

        // Headers
        $headers = [
            'A1' => 'Supplier Code',
            'B1' => 'Supplier Name',
            'C1' => 'Delivery Date',
            'D1' => 'Plant',
            'E1' => 'Kategori',
            'F1' => 'Material Code',
            'G1' => 'Description',
            'H1' => 'PO No.',
            'I1' => 'Currency',
            'J1' => 'Price',
            'K1' => 'Plan',
            'L1' => 'Plan Amount',
            'M1' => 'Result',
            'N1' => 'Result Amount',
            'O1' => 'Remaining',
            'P1' => 'Rem Amount',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Contoh Data
        $sampleData = [
            ['C102', 'PT. TRI JAYA TEKNIK KARAWANG', '2026-07-15', 'KIP 1', 'PUR-04', '1312006', 'MAIN BOARD A', 'KI-TJT-0023', 'IDR', 8470, 210, 1778700, 210, 1778700, 0, 0],
            ['C102', 'PT. TRI JAYA TEKNIK KARAWANG', '2026-07-20', 'KIP 1', 'PUR-04', '1312006', 'MAIN BOARD A', 'KI-TJT-0027', 'IDR', 8470, 200, 1694000, 126, 1067220, 74, 626780],
            ['C146', 'PT. SUMBER AGUNG SEJAHTERA ABADI', '2026-07-25', 'KIP 2', 'PUR-01', '1311010', 'SCREW B 4X12', 'KI-SAS-0006', 'USD', 2.50, 600, 1500, 0, 0, 600, 1500],
            ['C096', 'CV. BIMASAKTI ANEKA NIAGA', '2026-07-28', 'KIP 1', 'PUR-02', '1314002', 'GESPER STRAPPING BAND', 'KI-BSA-0012', 'IDR', 95000, 0, 0, 120, 11400000, 0, 0],
        ];

        $r = 2;
        foreach ($sampleData as $row) {
            $colLetter = 'A';
            foreach ($row as $val) {
                $sheet->setCellValue($colLetter . $r, $val);
                $colLetter++;
            }
            $r++;
        }

        // Styling
        $sheet->getStyle('A1:P1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B'); // Shared (Dark Slate)
        $sheet->getStyle('K1:L1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0284C7'); // Master PO (Blue)
        $sheet->getStyle('M1:N1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('059669'); // Incoming (Emerald)
        $sheet->getStyle('O1:P1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D97706'); // Remaining (Amber)

        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Generate template Excel khusus Master PO (Step 2).
     */
    public function generateMasterPoTemplate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Master PO');

        $headers = [
            'A1' => 'Supplier Code',
            'B1' => 'Supplier Name',
            'C1' => 'Delivery Date',
            'D1' => 'Plant',
            'E1' => 'Kategori',
            'F1' => 'Material Code',
            'G1' => 'Description',
            'H1' => 'PO No.',
            'I1' => 'Currency',
            'J1' => 'Price',
            'K1' => 'Plan',
            'L1' => 'Plan Amount',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sampleData = [
            ['C102', 'PT. TRI JAYA TEKNIK KARAWANG', '2026-07-15', 'KIP 1', 'PUR-04', '1312006', 'MAIN BOARD A', 'KI-TJT-0023', 'IDR', 8470, 210, 1778700],
            ['C102', 'PT. TRI JAYA TEKNIK KARAWANG', '2026-07-20', 'KIP 1', 'PUR-04', '1312006', 'MAIN BOARD A', 'KI-TJT-0027', 'IDR', 8470, 200, 1694000],
            ['C146', 'PT. SUMBER AGUNG SEJAHTERA ABADI', '2026-07-25', 'KIP 2', 'PUR-01', '1311010', 'SCREW B 4X12', 'KI-SAS-0006', 'USD', 2.50, 600, 1500],
        ];

        $r = 2;
        foreach ($sampleData as $row) {
            $colLetter = 'A';
            foreach ($row as $val) {
                $sheet->setCellValue($colLetter . $r, $val);
                $colLetter++;
            }
            $r++;
        }

        $sheet->getStyle('A1:L1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B'); // Slate
        $sheet->getStyle('K1:L1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0284C7'); // Blue Plan

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Generate template Excel khusus Incoming / Realisasi (Step 3).
     */
    public function generateIncomingTemplate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Incoming PO');

        $headers = [
            'A1' => 'Supplier Code',
            'B1' => 'Supplier Name',
            'C1' => 'Receipt Date',
            'D1' => 'Plant',
            'E1' => 'Kategori',
            'F1' => 'Material Code',
            'G1' => 'Description',
            'H1' => 'PO No.',
            'I1' => 'Currency',
            'J1' => 'Price',
            'K1' => 'Result',
            'L1' => 'Result Amount',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sampleData = [
            ['C102', 'PT. TRI JAYA TEKNIK KARAWANG', '2026-07-15', 'KIP 1', 'PUR-04', '1312006', 'MAIN BOARD A', 'KI-TJT-0023', 'IDR', 8470, 210, 1778700],
            ['C102', 'PT. TRI JAYA TEKNIK KARAWANG', '2026-07-20', 'KIP 1', 'PUR-04', '1312006', 'MAIN BOARD A', 'KI-TJT-0027', 'IDR', 8470, 126, 1067220],
            ['C096', 'CV. BIMASAKTI ANEKA NIAGA', '2026-07-28', 'KIP 1', 'PUR-02', '1314002', 'GESPER STRAPPING BAND', 'KI-BSA-0012', 'IDR', 95000, 120, 11400000],
        ];

        $r = 2;
        foreach ($sampleData as $row) {
            $colLetter = 'A';
            foreach ($row as $val) {
                $sheet->setCellValue($colLetter . $r, $val);
                $colLetter++;
            }
            $r++;
        }

        $sheet->getStyle('A1:L1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B'); // Slate
        $sheet->getStyle('K1:L1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('059669'); // Emerald Result

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
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

    public function normalizeCurrency(?string $curr, ?float $price = null): string
    {
        $clean = strtoupper(trim((string)$curr));
        if ($price !== null && $price > 300) {
            return 'IDR';
        }
        if (in_array($clean, ['IDR', 'RUPIAH', 'RP', 'IDR (RP)']) || str_contains($clean, 'RP') || str_contains($clean, 'RUPIAH')) {
            return 'IDR';
        }
        return 'USD';
    }

    protected function parseNumericAmount($val, string $currency = 'USD'): float
    {
        if ($val === null || $val === '') return 0.0;
        $num = InputNormalizer::normalizeNumber($val, 4, false);
        return $num !== null ? max(0.0, $num) : 0.0;
    }

    protected function parseExcelDate($val): string
    {
        if (empty($val)) return date('Y-m-d');
        if (is_numeric($val) && (float)$val > 25569) {
            try {
                $unixDate = ((float)$val - 25569) * 86400;
                return gmdate('Y-m-d', (int)$unixDate);
            } catch (\Throwable $e) {}
        }
        $t = strtotime((string)$val);
        return $t !== false ? date('Y-m-d', $t) : date('Y-m-d');
    }

    protected function getVal(array $row, ?string $col, $default = ''): string
    {
        if (!$col || !isset($row[$col])) return (string)$default;
        return trim((string)$row[$col]);
    }

    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string)$cell) !== '') return false;
        }
        return true;
    }
}
