<?php

namespace App\Http\Controllers;

use App\Services\Import\IntegratedPoIncomingImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IntegratedImportController extends Controller
{
    protected IntegratedPoIncomingImporter $importer;

    public function __construct(IntegratedPoIncomingImporter $importer)
    {
        $this->importer = $importer;
    }

    /**
     * Preview dan Analisis File Excel Terpadu PO & Incoming.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file'     => 'required|file|max:10240',
            'currency' => 'nullable|string',
            'context'  => 'nullable|string|in:MASTER_PO,INCOMING,INTEGRATED',
        ]);

        try {
            $file = $request->file('file');
            $realPath = $file->getRealPath();
            $defaultCurrency = $request->input('currency', 'USD');
            if (!in_array(strtoupper($defaultCurrency), ['USD', 'IDR'], true)) {
                $defaultCurrency = 'USD';
            }

            $context = strtoupper($request->input('context', 'INTEGRATED'));

            $result = $this->importer->parseAndAnalyze($realPath, [
                'file_name'        => $file->getClientOriginalName(),
                'default_currency' => $defaultCurrency,
                'context'          => $context,
            ]);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error'   => $result['error'] ?? 'Gagal membaca file Excel.',
                ], 422);
            }

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import Khusus Master PO (Step 2) — Hanya Memproses Kolom Plan.
     */
    public function importMasterPo(Request $request)
    {
        $request->validate([
            'file'     => 'required|file|max:10240',
            'currency' => 'nullable|string',
        ]);

        try {
            $file = $request->file('file');
            $currency = $request->input('currency', 'USD');
            if (!in_array(strtoupper($currency), ['USD', 'IDR'], true)) {
                $currency = 'USD';
            }

            $analysis = $this->importer->parseAndAnalyze($file->getRealPath(), [
                'file_name'        => $file->getClientOriginalName(),
                'default_currency' => $currency,
                'context'          => 'MASTER_PO',
            ]);

            if (!$analysis['success']) {
                return redirect()->back()->with('error', $analysis['error']);
            }

            if (empty($analysis['master_po_rows'])) {
                return redirect()->back()->with('error', 'Tidak ada data Plan / PO yang valid terbaca dari file Excel.');
            }

            $userId = Auth::id() ?? 1;
            $importResult = $this->importer->executeImport($analysis, $userId);

            if (!$importResult['success']) {
                return redirect()->back()->with('error', $importResult['error']);
            }

            $poCount = $importResult['inserted_master_po'];
            $ignoredResult = $analysis['reconciliation']['ignored_result_rows'] ?? 0;
            $msg = "<b>✓ Master PO berhasil diimport!</b> {$poCount} data PO berhasil ditambahkan.";
            if ($ignoredResult > 0) {
                $msg .= " ({$ignoredResult} baris kolom Result diabaikan karena proses ini adalah import Master PO).";
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal memproses import Master PO: ' . $e->getMessage());
        }
    }

    /**
     * Import Khusus Incoming / Realisasi (Step 3) — Hanya Memproses Kolom Result.
     */
    public function importIncoming(Request $request)
    {
        $request->validate([
            'file'     => 'required|file|max:10240',
            'currency' => 'nullable|string',
        ]);

        try {
            $file = $request->file('file');
            $currency = $request->input('currency', 'USD');
            if (!in_array(strtoupper($currency), ['USD', 'IDR'], true)) {
                $currency = 'USD';
            }

            $analysis = $this->importer->parseAndAnalyze($file->getRealPath(), [
                'file_name'        => $file->getClientOriginalName(),
                'default_currency' => $currency,
                'context'          => 'INCOMING',
            ]);

            if (!$analysis['success']) {
                return redirect()->back()->with('error', $analysis['error']);
            }

            if (empty($analysis['incoming_rows'])) {
                return redirect()->back()->with('error', 'Tidak ada data Result / Penerimaan yang valid terbaca dari file Excel.');
            }

            $userId = Auth::id() ?? 1;
            $importResult = $this->importer->executeImport($analysis, $userId);

            if (!$importResult['success']) {
                return redirect()->back()->with('error', $importResult['error']);
            }

            $incCount = $importResult['inserted_incoming'];
            $ignoredPlan = $analysis['reconciliation']['ignored_plan_rows'] ?? 0;
            $msg = "<b>✓ Incoming berhasil diimport!</b> {$incCount} data penerimaan berhasil ditambahkan.";
            if ($ignoredPlan > 0) {
                $msg .= " ({$ignoredPlan} baris data Plan tidak diimport pada proses ini).";
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal memproses import Incoming: ' . $e->getMessage());
        }
    }

    /**
     * Eksekusi Commit Data Hasil Analisis ke Database (General / Integrated).
     */
    public function execute(Request $request)
    {
        if ($request->hasFile('file')) {
            $request->validate([
                'file'     => 'required|file|max:10240',
                'currency' => 'nullable|string',
                'context'  => 'nullable|string',
            ]);

            $file = $request->file('file');
            $currency = $request->input('currency', 'USD');
            if (!in_array(strtoupper($currency), ['USD', 'IDR'], true)) {
                $currency = 'USD';
            }

            $context = strtoupper($request->input('context', 'INTEGRATED'));

            $analysis = $this->importer->parseAndAnalyze($file->getRealPath(), [
                'file_name'        => $file->getClientOriginalName(),
                'default_currency' => $currency,
                'context'          => $context,
            ]);

            if (!$analysis['success']) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'error' => $analysis['error']], 422);
                }
                return redirect()->back()->with('error', $analysis['error']);
            }
        } else {
            $analysis = $request->input('analysis_data');
            if (is_string($analysis)) {
                $analysis = json_decode($analysis, true);
            }

            if (empty($analysis) || (!isset($analysis['master_po_rows']) && !isset($analysis['incoming_rows']))) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Data analisis import tidak ditemukan atau format tidak valid.',
                ], 422);
            }
        }

        $userId = Auth::id() ?? 1;
        $importResult = $this->importer->executeImport($analysis, $userId);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($importResult);
        }

        if ($importResult['success']) {
            return redirect()->back()->with('success', $importResult['message']);
        } else {
            return redirect()->back()->with('error', $importResult['error']);
        }
    }

    /**
     * Download Template Resmi Master PO (Step 2).
     */
    public function downloadMasterPoTemplate(): StreamedResponse
    {
        $spreadsheet = $this->importer->generateMasterPoTemplate();
        $fileName = 'template_master_po.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Download Template Resmi Incoming (Step 3).
     */
    public function downloadIncomingTemplate(): StreamedResponse
    {
        $spreadsheet = $this->importer->generateIncomingTemplate();
        $fileName = 'template_incoming.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Download Template Resmi Terpadu PO & Incoming.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $spreadsheet = $this->importer->generateTemplate();
        $fileName = 'Template_PO_Incoming_Integrated.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Smart Preview: Menggunakan Document Intelligence Pipeline.
     * Mendeteksi tipe dokumen, memetakan kolom secara otomatis,
     * memvalidasi data, dan menampilkan preview sebelum import.
     */
    public function smartPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'currency' => 'nullable|string',
        ]);

        try {
            $file = $request->file('file');
            $filePath = $file->getRealPath();
            $fileName = $file->getClientOriginalName();
            $defaultCurrency = strtoupper($request->input('currency', 'USD'));
            
            // 1. Register document and check for duplicates
            $duplicateDetector = new \App\Services\Import\GenericDuplicateDetector();
            $existingDocId = $duplicateDetector->detectFileDuplicate($filePath);
            
            if ($existingDocId) {
                return response()->json([
                    'success' => true,
                    'duplicate_warning' => true,
                    'existing_document_id' => $existingDocId,
                    'message' => 'File ini sudah pernah diupload sebelumnya. Lanjutkan untuk meng-overwrite?',
                ]);
            }
            
            // 2. Read spreadsheet
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
            if (method_exists($reader, 'setReadDataOnly')) {
                $reader->setReadDataOnly(true);
            }
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rawRows = $sheet->toArray(null, false, true, true);
            
            if (empty($rawRows)) {
                return response()->json(['success' => false, 'error' => 'File Excel kosong.'], 422);
            }
            
            // 3. Detect header row
            $headerDetector = new \App\Services\Document\HeaderDetector();
            $headerResult = $headerDetector->detect($rawRows);
            
            // 4. Detect document type
            $headerRow = $rawRows[$headerResult['header_row_index']] ?? [];
            $sampleRows = array_slice($rawRows, $headerResult['data_start_index'], 5, true);
            $docTypeDetector = new \App\Services\Document\DocumentTypeDetector();
            $typeResult = $docTypeDetector->detect($headerRow, $sampleRows, $fileName);
            
            // 5. Map columns
            $aboveRow = isset($rawRows[$headerResult['header_row_index'] - 1]) 
                ? $rawRows[$headerResult['header_row_index'] - 1] : null;
            $columnMapper = new \App\Services\Document\ColumnMapper();
            $mappingResult = $columnMapper->map($headerRow, $aboveRow, $typeResult['type']);
            
            // 6. Create Document record
            $fileHash = hash_file('sha256', $filePath);
            $document = \App\Models\Document::create([
                'filename' => $fileName,
                'original_filename' => $fileName,
                'file_hash' => $fileHash,
                'file_size' => filesize($filePath),
                'document_type' => $typeResult['type'],
                'document_type_confidence' => $typeResult['confidence'],
                'detected_header_row' => $headerResult['header_row_index'],
                'total_rows' => count($rawRows) - $headerResult['data_start_index'],
                'total_columns' => count($headerRow),
                'status' => 'ANALYZING',
                'uploaded_by' => \Illuminate\Support\Facades\Auth::id(),
            ]);
            
            // 7. Save column mappings
            foreach ($mappingResult['mappings'] as $mapping) {
                \App\Models\ColumnMapping::create([
                    'document_id' => $document->id,
                    'source_column_letter' => $mapping['column_letter'],
                    'source_column_name' => $mapping['source_name'],
                    'canonical_field' => $mapping['canonical_field'],
                    'confidence' => $mapping['confidence'],
                    'mapping_method' => $mapping['method'],
                ]);
            }
            
            // 8. Profile the document
            $profiler = new \App\Services\Document\DocumentProfiler();
            $dataRows = array_slice($rawRows, $headerResult['data_start_index'], null, true);
            
            // Build column map for profiler (canonical_field => column_letter)
            $colMap = [];
            foreach ($mappingResult['mappings'] as $m) {
                $colMap[$m['canonical_field']] = $m['column_letter'];
            }
            $profileData = $profiler->profile($document, $dataRows, $colMap);
            
            // 9. Update document with profile
            $document->update([
                'date_range_min' => $profileData['date_range_min'] ?? null,
                'date_range_max' => $profileData['date_range_max'] ?? null,
                'unique_items' => $profileData['unique_items'] ?? 0,
                'unique_pos' => $profileData['unique_pos'] ?? 0,
                'unique_suppliers' => $profileData['unique_suppliers'] ?? 0,
                'currency_distribution' => $profileData['currency_distribution'] ?? null,
                'profile_data' => $profileData,
                'status' => 'MAPPED',
            ]);
            
            // 10. Return preview response
            return response()->json([
                'success' => true,
                'document_id' => $document->id,
                'document_type' => [
                    'detected' => $typeResult['type'],
                    'confidence' => $typeResult['confidence'],
                    'alternatives' => $typeResult['alternatives'] ?? [],
                ],
                'header' => [
                    'row_index' => $headerResult['header_row_index'],
                    'confidence' => $headerResult['confidence'],
                    'metadata' => $headerResult['metadata_rows'] ?? [],
                ],
                'column_mappings' => $mappingResult['mappings'],
                'unmapped_columns' => $mappingResult['unmapped_columns'] ?? [],
                'missing_required' => $mappingResult['missing_required'] ?? [],
                'overall_mapping_confidence' => $mappingResult['overall_confidence'] ?? 0,
                'profile' => $profileData,
                'preview_rows' => array_slice($dataRows, 0, 10, true),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error saat analisis dokumen: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Smart Import: Execute import using Document Intelligence pipeline.
     */
    public function smartImport(Request $request)
    {
        $request->validate([
            'document_id' => 'required|integer|exists:documents,id',
            'file' => 'required|file|max:10240',
            'type_override' => 'nullable|string',
            'mapping_overrides' => 'nullable|array',
        ]);

        try {
            $document = \App\Models\Document::findOrFail($request->input('document_id'));
            $file = $request->file('file');
            $filePath = $file->getRealPath();
            $userId = \Illuminate\Support\Facades\Auth::id() ?? 1;

            // Apply type override if user changed it
            $docType = strtoupper($request->input('type_override', $document->document_type));
            if ($docType !== $document->document_type) {
                $document->update(['document_type' => $docType]);
            }

            // Create import session
            $session = \App\Models\ImportSession::create([
                'document_id' => $document->id,
                'session_code' => 'IMP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
                'status' => 'IMPORTING',
                'imported_by' => $userId,
                'started_at' => now(),
            ]);

            // Re-parse file using existing importer but with correct context
            $context = match ($docType) {
                'MASTER_PO' => 'MASTER_PO',
                'INCOMING' => 'INCOMING',
                default => 'INTEGRATED',
            };

            $analysis = $this->importer->parseAndAnalyze($filePath, [
                'file_name' => $file->getClientOriginalName(),
                'default_currency' => 'USD',
                'context' => $context,
            ]);

            if (!$analysis['success']) {
                $session->markFailed($analysis['error'] ?? 'Gagal parsing file.');
                return response()->json(['success' => false, 'error' => $analysis['error']], 422);
            }

            // Validate rows using GenericValidationService
            $validator = new \App\Services\Import\GenericValidationService();
            $allRows = array_merge($analysis['master_po_rows'] ?? [], $analysis['incoming_rows'] ?? []);
            $validationResult = $validator->validateRows($allRows, $docType, $document->id);

            // Save quality issues
            foreach ($validationResult['issues'] as $issue) {
                \App\Models\DataQualityIssue::create(array_merge($issue, [
                    'document_id' => $document->id,
                    'import_session_id' => $session->id,
                ]));
            }

            // Execute import using existing importer
            $importResult = $this->importer->executeImport($analysis, $userId);

            // Update session
            $session->update([
                'status' => $importResult['success'] ? 'COMPLETED' : 'FAILED',
                'total_rows' => count($allRows),
                'valid_count' => $validationResult['summary']['valid'] ?? 0,
                'warning_count' => $validationResult['summary']['warnings'] ?? 0,
                'error_count' => $validationResult['summary']['errors'] ?? 0,
                'inserted_po_count' => $importResult['inserted_master_po'] ?? 0,
                'inserted_incoming_count' => $importResult['inserted_incoming'] ?? 0,
                'completed_at' => now(),
                'error_message' => $importResult['error'] ?? null,
            ]);

            // Update document status
            $document->update(['status' => $importResult['success'] ? 'COMPLETED' : 'FAILED']);

            // Learn from confirmed mappings (adaptive learning)
            $mapper = new \App\Services\Document\ColumnMapper();
            $confirmedMappings = $document->columnMappings()->get();
            foreach ($confirmedMappings as $cm) {
                if ($cm->source_column_name && $cm->canonical_field) {
                    $mapper->learnFromConfirmation($cm->source_column_name, $cm->canonical_field);
                }
            }

            return response()->json([
                'success' => $importResult['success'],
                'message' => $importResult['message'] ?? 'Import selesai.',
                'session_id' => $session->id,
                'imported' => [
                    'master_po' => $importResult['inserted_master_po'] ?? 0,
                    'incoming' => $importResult['inserted_incoming'] ?? 0,
                ],
                'quality' => $validationResult['summary'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Import gagal: ' . $e->getMessage(),
            ], 500);
        }
    }
}
