<?php

namespace App\Services\DataValidation;

/**
 * SchemaValidator
 * 
 * Validator struktur file dan versi template resmi untuk sistem purchasing.
 * Mencegah impor parsial diam-diam jika header atau kolom esensial tidak sesuai.
 */
class SchemaValidator
{
    /**
     * Definisi Skema Resmi per Template
     */
    protected static array $schemas = [
        'forecast' => [
            'name' => 'Master Forecast Template',
            'current_version' => '2.0',
            'supported_versions' => ['1.0', '1.1', '2.0'],
            'required_columns' => [
                'material_code' => ['item_code', 'part_number', 'kode_barang', 'material_code', 'part_no', 'drawing'],
                'target_qty'    => ['target_order', 'target_qty', 'forecast_qty', 'qty_target', 'jumlah_target'],
            ],
            'optional_columns' => [
                'supplier'      => ['supplier', 'vendor', 'supplier_name', 'nama_supplier'],
                'price'         => ['price', 'unit_price', 'harga', 'harga_satuan', 'price_usd'],
                'currency'      => ['currency', 'mata_uang', 'curr'],
                'factory_code'  => ['factory_code', 'plant', 'pabrik', 'lokasi'],
            ]
        ],
        'master_po' => [
            'name' => 'Master Purchase Order Template',
            'current_version' => '1.2',
            'supported_versions' => ['1.0', '1.1', '1.2'],
            'required_columns' => [
                'po'            => ['po', 'po_number', 'no_po', 'nomor_po', 'purchase_order'],
                'item_code'     => ['item_code', 'part_number', 'kode_barang', 'material_code', 'part_no'],
                'qty'           => ['qty', 'order_qty', 'kuantitas', 'jumlah_order', 'qty_po'],
            ],
            'optional_columns' => [
                'supplier'      => ['supplier', 'vendor', 'supplier_name', 'nama_supplier'],
                'price'         => ['price', 'unit_price', 'harga', 'price_usd'],
                'currency'      => ['currency', 'curr'],
                'eta'           => ['eta', 'eta_date', 'tanggal_eta', 'arrival_date', 'jadwal_tiba'],
                'factory_code'  => ['factory_code', 'plant', 'pabrik'],
            ]
        ],
        'incoming' => [
            'name' => 'Incoming Penerimaan PO Template',
            'current_version' => '1.1',
            'supported_versions' => ['1.0', '1.1'],
            'required_columns' => [
                'item_code'       => ['item_code', 'part_number', 'kode_barang', 'material_code'],
                'actual_received' => ['actual_received', 'qty_received', 'diterima', 'jumlah_diterima', 'aktual_masuk'],
            ],
            'optional_columns' => [
                'po_reference'    => ['po_reference', 'po', 'no_po', 'nomor_po', 'po_number'],
                'supplier'        => ['supplier', 'vendor', 'supplier_name'],
                'date'            => ['date', 'tanggal', 'receipt_date', 'tgl_terima', 'period_month'],
                'iad_status'      => ['iad_status', 'status_iad', 'quality_status', 'pemeriksaan_iad'],
                'price'           => ['price', 'harga', 'unit_price'],
            ]
        ],
        'actual_production' => [
            'name' => 'Actual Production Template',
            'current_version' => '1.1',
            'supported_versions' => ['1.0', '1.1'],
            'required_columns' => [
                'material_code'   => ['material_code', 'item_code', 'part_number', 'kode_barang', 'part_no'],
                'production_qty'  => ['production_qty', 'actual_production', 'output_qty', 'hasil_produksi', 'qty_produksi', 'qty'],
            ],
            'optional_columns' => [
                'supplier_code'   => ['supplier_code', 'kode_supplier', 'vendor_code'],
                'supplier_name'   => ['supplier_name', 'supplier', 'vendor', 'nama_supplier'],
                'plant'           => ['plant', 'factory_code', 'pabrik', 'plant_code'],
                'description'     => ['description', 'item_name', 'nama_barang', 'deskripsi'],
                'production_date' => ['production_date', 'date', 'tanggal', 'periode', 'period_month', 'snapshot_date'],
            ]
        ],
        'actual_inventory' => [
            'name' => 'Actual Inventory Template',
            'current_version' => '1.1',
            'supported_versions' => ['1.0', '1.1'],
            'required_columns' => [
                'material_code'    => ['material_code', 'item_code', 'part_number', 'kode_barang', 'part_no'],
                'actual_inventory' => ['actual_inventory', 'current_stock', 'stok_fisik', 'stok_aktual', 'qty_inventory', 'qty', 'inventory_qty'],
            ],
            'optional_columns' => [
                'supplier_code'    => ['supplier_code', 'kode_supplier', 'vendor_code'],
                'supplier_name'    => ['supplier_name', 'supplier', 'vendor', 'nama_supplier'],
                'plant'            => ['plant', 'factory_code', 'pabrik', 'plant_code'],
                'description'      => ['description', 'item_name', 'nama_barang', 'deskripsi'],
                'snapshot_date'    => ['snapshot_date', 'tanggal_inventory', 'stock_date', 'tanggal_opname', 'date'],
                'unit_price'       => ['unit_price', 'price', 'harga'],
                'warehouse_loc'    => ['warehouse_location', 'lokasi_gudang', 'warehouse'],
            ]
        ],
        'tax_exchange_rate' => [
            'name' => 'Master Kurs Pajak KMK Template',
            'current_version' => '1.0',
            'supported_versions' => ['1.0'],
            'required_columns' => [
                'currency'         => ['currency', 'mata_uang', 'curr'],
                'rate'             => ['rate', 'nilai_kurs', 'kurs', 'exchange_rate', 'kurs_pajak'],
                'start_date'       => ['start_date', 'tanggal_mulai', 'tgl_awal', 'valid_from'],
                'end_date'         => ['end_date', 'tanggal_akhir', 'tgl_akhir', 'valid_to'],
            ],
            'optional_columns' => [
                'kmk_number'       => ['kmk_number', 'no_kmk', 'nomor_kmk', 'kmk_ref'],
            ]
        ]
    ];

    /**
     * Validasi Header dan Skema Data
     * 
     * @param string $templateType Tipe template (forecast, master_po, incoming, actual_production, actual_inventory, tax_exchange_rate)
     * @param array $headers Daftar nama kolom yang terdeteksi pada baris pertama file Excel
     * @return array [ 'is_valid' => bool, 'column_mapping' => array, 'missing_required' => array, 'version_detected' => ?string, 'errors' => array ]
     */
    public static function validateHeaderSchema(string $templateType, array $headers): array
    {
        if (!isset(self::$schemas[$templateType])) {
            return [
                'is_valid' => false,
                'column_mapping' => [],
                'missing_required' => [],
                'version_detected' => null,
                'errors' => ["Tipe template '{$templateType}' tidak dikenali oleh sistem validation layer."]
            ];
        }

        $schema = self::$schemas[$templateType];
        $normalizedHeaders = [];
        foreach ($headers as $idx => $header) {
            $cleaned = strtolower(trim((string) $header));
            $cleaned = preg_replace('/[\x{00A0}\x{200B}\x{FEFF}]/u', '', $cleaned);
            $cleaned = preg_replace('/[^a-z0-9_]/', '_', $cleaned);
            $cleaned = preg_replace('/_+/', '_', $cleaned);
            $cleaned = trim($cleaned, '_');
            $normalizedHeaders[$idx] = $cleaned;
        }

        $columnMapping = [];
        $missingRequired = [];

        // 1. Cek Required Columns
        foreach ($schema['required_columns'] as $canonicalKey => $aliases) {
            $foundIndex = null;
            foreach ($normalizedHeaders as $idx => $h) {
                if (in_array($h, $aliases, true)) {
                    $foundIndex = $idx;
                    break;
                }
            }

            if ($foundIndex !== null) {
                $columnMapping[$canonicalKey] = $foundIndex;
            } else {
                $missingRequired[] = $canonicalKey;
            }
        }

        // 2. Cek Optional Columns
        foreach ($schema['optional_columns'] as $canonicalKey => $aliases) {
            foreach ($normalizedHeaders as $idx => $h) {
                if (in_array($h, $aliases, true) && !in_array($idx, $columnMapping, true)) {
                    $columnMapping[$canonicalKey] = $idx;
                    break;
                }
            }
        }

        // 3. Deteksi metadata versi jika disertakan di header
        $versionDetected = $schema['current_version'];
        foreach ($headers as $h) {
            if (preg_match('/v(\d+\.\d+)/i', (string) $h, $m)) {
                $versionDetected = $m[1];
                break;
            }
        }

        $isValid = count($missingRequired) === 0;
        $errors = [];

        if (!$isValid) {
            $missingList = implode(', ', array_map(function($key) {
                return strtoupper(str_replace('_', ' ', $key));
            }, $missingRequired));

            $errors[] = "Struktur template {$schema['name']} tidak valid. Kolom wajib berikut tidak ditemukan: [{$missingList}]. Harap gunakan format template resmi PT Kawai Indonesia.";
        }

        return [
            'is_valid' => $isValid,
            'template_name' => $schema['name'],
            'current_version' => $schema['current_version'],
            'version_detected' => $versionDetected,
            'column_mapping' => $columnMapping,
            'missing_required' => $missingRequired,
            'detected_headers' => $normalizedHeaders,
            'errors' => $errors
        ];
    }

    /**
     * Dapatkan Schema Template
     */
    public static function getSchema(string $templateType): ?array
    {
        return self::$schemas[$templateType] ?? null;
    }
}
