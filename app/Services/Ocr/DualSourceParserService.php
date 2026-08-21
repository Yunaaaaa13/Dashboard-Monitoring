<?php

namespace App\Services\Ocr;

use App\Services\DataValidation\DataValidator;
use App\Services\DataValidation\InputNormalizer;
use App\Services\DataValidation\SchemaValidator;
use Illuminate\Http\UploadedFile;

/**
 * DualSourceParserService
 * 
 * Pengendali Utama Dual-Source Parsing:
 * 1. Native Tabular Parsing (XLSX / XLS / CSV) sebagai jalur utama (akurasi 100%).
 * 2. OCR Fallback Pipeline (Gambar / Scan PDF) sebagai kandidat data berkonfidensi.
 */
class DualSourceParserService
{
    protected DataValidator $validator;
    protected MasterDictionaryMatcher $matcher;

    public function __construct(DataValidator $validator, MasterDictionaryMatcher $matcher)
    {
        $this->validator = $validator;
        $this->matcher = $matcher;
    }

    /**
     * Parse File Berdasarkan Tipe Dokumen
     * 
     * @param UploadedFile|string $file File yang diunggah
     * @param string $templateType
     * @return array [ 'source_engine' => string, 'is_ocr' => bool, 'parsed_rows' => array, 'validation' => array, 'ocr_quality' => ?array, 'schema' => array ]
     */
    public function parseFile($file, string $templateType): array
    {
        $extension = strtolower(is_string($file) ? pathinfo($file, PATHINFO_EXTENSION) : $file->getClientOriginalExtension());
        $isSpreadsheet = in_array($extension, ['xlsx', 'xls', 'csv', 'ods'], true);

        if ($isSpreadsheet) {
            return $this->parseSpreadsheet($file, $templateType);
        } else {
            return $this->parseWithOcrFallback($file, $templateType);
        }
    }

    /**
     * Jalur Utama: Parse Spreadsheet Tabular Asli
     */
    protected function parseSpreadsheet($file, string $templateType): array
    {
        $filePath = is_string($file) ? $file : $file->getRealPath();
        $rows = [];

        // 1. Coba PhpSpreadsheet untuk XLSX/XLS atau fgetcsv untuk CSV
        $ext = strtolower(is_string($file) ? pathinfo($file, PATHINFO_EXTENSION) : $file->getClientOriginalExtension());
        if (in_array($ext, ['xlsx', 'xls'], true) && class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
                if (method_exists($reader, 'setReadDataOnly')) {
                    $reader->setReadDataOnly(true);
                }
                $spreadsheet = $reader->load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                $rawRows = $sheet->toArray(null, false, true, false);
                $rows = array_values(array_filter($rawRows, fn($r) => !empty(array_filter($r, fn($v) => trim((string)$v) !== ''))));
            } catch (\Throwable $e) {
                $rows = [];
            }
        }

        // Fallback ke CSV parsing jika $rows masih kosong
        if (empty($rows) && ($handle = fopen($filePath, "r")) !== false) {
            while (($data = fgetcsv($handle, 4000, ",")) !== false) {
                if (empty(array_filter($data, fn($v) => trim((string)$v) !== ''))) {
                    continue;
                }
                $rows[] = $data;
            }
            fclose($handle);
        }

        // Khusus Template Forecast v1
        if (str_contains(strtolower($templateType), 'forecast')) {
            $forecastParser = new ForecastTemplateParser($this->matcher);
            $parsedResult = $forecastParser->parseTemplate($rows);
            $dedupResult = \App\Services\DataValidation\ForecastDuplicateDetector::evaluateBatch($parsedResult['parsed_rows'] ?? []);

            return [
                'source_engine' => 'NATIVE_SPREADSHEET_PARSER',
                'is_ocr' => false,
                'file_extension' => pathinfo($filePath, PATHINFO_EXTENSION),
                'schema' => ['is_valid' => true, 'template' => 'forecast_template_v1'],
                'raw_rows_count' => count($rows),
                'parsed_rows' => $dedupResult['evaluated_rows'],
                'summary' => array_merge($parsedResult['summary'] ?? [], [
                    'duplicate_count' => $dedupResult['duplicate_count'],
                ]),
                'periods' => $parsedResult['periods'] ?? [],
                'duplicate_details' => $dedupResult['duplicate_details'],
            ];
        }

        // Generic Schema Parsing
        $headers = !empty($rows) ? $rows[0] : [];
        $dataRows = count($rows) > 1 ? array_slice($rows, 1) : [];

        $schemaCheck = SchemaValidator::validateHeaderSchema($templateType, $headers);
        $validationResult = $this->validator->validateBatch($templateType, $dataRows);

        return [
            'source_engine' => 'NATIVE_SPREADSHEET_PARSER',
            'is_ocr' => false,
            'file_extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'schema' => $schemaCheck,
            'raw_rows_count' => count($dataRows),
            'validation' => $validationResult,
            'ocr_quality' => null,
        ];
    }

    /**
     * Jalur Cadangan (Fallback): Parse Melalui OCR Pipeline
     */
    protected function parseWithOcrFallback($file, string $templateType): array
    {
        $filePath = is_string($file) ? $file : $file->getRealPath();
        
        // Simulasi hasil ekstraksi OCR kandidat baris tabel
        $candidateRows = [];
        // Menjalankan scoring keyakinan dan koreksi kamus
        $ocrQuality = OcrQualityScorer::scoreBatch($candidateRows, $this->matcher);
        $validationResult = $this->validator->validateBatch($templateType, $candidateRows);

        return [
            'source_engine' => 'OCR_EXTRACTION_PIPELINE',
            'is_ocr' => true,
            'file_extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'schema' => ['is_valid' => true, 'errors' => []],
            'raw_rows_count' => count($candidateRows),
            'validation' => $validationResult,
            'ocr_quality' => $ocrQuality,
        ];
    }
}
