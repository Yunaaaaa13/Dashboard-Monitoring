<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\DataValidation\ForecastDuplicateDetector;

class ForecastDuplicateDetectorTest extends TestCase
{
    public function test_same_material_different_suppliers_is_not_duplicate()
    {
        $rows = [
            [
                'source_row_number' => 1,
                'material_code' => '1311004',
                'supplier_name' => 'SUPPLIER A',
                'supplier_code' => 'SUP_A',
                'plant' => 'PLANT 3',
                'monthly_data' => [
                    '2026-07' => ['forecast_qty' => 100],
                ],
                'warnings' => [],
            ],
            [
                'source_row_number' => 2,
                'material_code' => '1311004',
                'supplier_name' => 'SUPPLIER B',
                'supplier_code' => 'SUP_B',
                'plant' => 'PLANT 3',
                'monthly_data' => [
                    '2026-07' => ['forecast_qty' => 150],
                ],
                'warnings' => [],
            ],
        ];

        $result = ForecastDuplicateDetector::evaluateBatch($rows);

        $this->assertEquals(0, $result['duplicate_count']);
        $this->assertNotEquals('DUPLICATE_WARNING', $result['evaluated_rows'][0]['status'] ?? '');
        $this->assertNotEquals('DUPLICATE_WARNING', $result['evaluated_rows'][1]['status'] ?? '');
    }

    public function test_same_material_different_months_is_not_duplicate()
    {
        $rows = [
            [
                'source_row_number' => 1,
                'material_code' => '1311004',
                'supplier_name' => 'SUPPLIER A',
                'supplier_code' => 'SUP_A',
                'plant' => 'PLANT 3',
                'monthly_data' => [
                    '2026-07' => ['forecast_qty' => 100],
                    '2026-08' => ['forecast_qty' => 200],
                ],
                'warnings' => [],
            ],
        ];

        $result = ForecastDuplicateDetector::evaluateBatch($rows);

        $this->assertEquals(0, $result['duplicate_count']);
    }

    public function test_identical_material_supplier_plant_period_detected_as_duplicate()
    {
        $rows = [
            [
                'source_row_number' => 5,
                'material_code' => '1312006',
                'supplier_name' => 'PT SUMBER AGUNG',
                'supplier_code' => 'C146',
                'plant' => 'PLANT 3',
                'monthly_data' => [
                    '2026-07' => ['forecast_qty' => 100],
                ],
                'warnings' => [],
            ],
            [
                'source_row_number' => 8,
                'material_code' => '1312006',
                'supplier_name' => 'PT SUMBER AGUNG',
                'supplier_code' => 'C146',
                'plant' => 'PLANT 3',
                'monthly_data' => [
                    '2026-07' => ['forecast_qty' => 100],
                ],
                'warnings' => [],
            ],
        ];

        $result = ForecastDuplicateDetector::evaluateBatch($rows);

        $this->assertEquals(1, $result['duplicate_count']);
        $this->assertEquals('DUPLICATE_WARNING', $result['evaluated_rows'][1]['status']);
    }
}
