<?php

namespace Tests\Unit;

use App\Services\DataValidation\DataValidator;
use App\Services\DataValidation\InputNormalizer;
use App\Services\DataValidation\SchemaValidator;
use Tests\TestCase;

class DataValidatorTest extends TestCase
{
    /**
     * Test InputNormalizer: Leading Zeroes & Number Formats
     */
    public function test_input_normalizer_material_code(): void
    {
        // Leading zero harus dipertahankan
        $this->assertEquals('001234', InputNormalizer::normalizeMaterialCode('001234'));
        $this->assertEquals('1312006', InputNormalizer::normalizeMaterialCode(' 1312006 '));
        $this->assertEquals('GP-BRACKET', InputNormalizer::normalizeMaterialCode('gp-bracket '));
    }

    public function test_input_normalizer_numbers(): void
    {
        // Format Internasional US (1,250.50)
        $this->assertEquals(1250.50, InputNormalizer::normalizeNumber('1,250.50'));

        // Format Indonesia (1.250,50)
        $this->assertEquals(1250.50, InputNormalizer::normalizeNumber('1.250,50'));

        // Dengan simbol mata uang
        $this->assertEquals(50000.0, InputNormalizer::normalizeNumber('Rp 50.000'));
        $this->assertEquals(125.75, InputNormalizer::normalizeNumber('$ 125.75 USD'));

        // String non-angka
        $this->assertNull(InputNormalizer::normalizeNumber('ABC'));
    }

    /**
     * Test SchemaValidator
     */
    public function test_schema_validator_headers(): void
    {
        // Header Forecast Valid
        $validHeaders = ['Item Code', 'Target Order', 'Supplier', 'Price USD'];
        $res = SchemaValidator::validateHeaderSchema('forecast', $validHeaders);
        $this->assertTrue($res['is_valid']);
        $this->assertEmpty($res['missing_required']);

        // Header Tidak Lengkap (Hilang target_qty)
        $invalidHeaders = ['Item Code', 'Supplier', 'Factory'];
        $resInvalid = SchemaValidator::validateHeaderSchema('forecast', $invalidHeaders);
        $this->assertFalse($resInvalid['is_valid']);
        $this->assertContains('target_qty', $resInvalid['missing_required']);
    }

    /**
     * Test DataValidator: Row Validation & Business Rules
     */
    public function test_data_validator_actual_production_row(): void
    {
        $validator = new DataValidator();

        // Baris Valid
        $validRow = [
            'material_code' => '1312006',
            'production_qty' => '250',
            'plant' => 'Plant 3',
            'supplier_name' => 'PT. SERBAGUNA PRIMA',
            'production_date' => '2026-08-19'
        ];
        $resValid = $validator->validateRow('actual_production', $validRow, 1);
        $this->assertTrue($resValid['is_valid']);
        $this->assertEquals('VALID', $resValid['status']);
        $this->assertEquals(250, $resValid['normalized']['production_qty']);

        // Baris Invalid: Qty Negatif
        $invalidRow = [
            'material_code' => '1312006',
            'production_qty' => '-50',
            'plant' => 'Plant 3'
        ];
        $resInvalid = $validator->validateRow('actual_production', $invalidRow, 2);
        $this->assertFalse($resInvalid['is_valid']);
        $this->assertEquals('INVALID', $resInvalid['status']);
    }
}
