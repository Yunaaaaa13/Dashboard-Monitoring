<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Ocr\ForecastTemplateParser;
use App\Services\Ocr\MasterDictionaryMatcher;

class ForecastTemplateParserTest extends TestCase
{
    public function test_forecast_template_parser_extracts_3_level_headers_and_monthly_blocks()
    {
        $rawMatrix = [
            // Row 1: Top Header Level 1 (Periods)
            ['SUPPLIER CODE', 'SUPPLIER NAME', 'PLANT', 'MATERIAL CODE', 'DESCRIPTION', 'UNIT PRICE', 'CURRENCY', 'JUL-26', '', '', '', '', 'AUG-26', '', '', '', ''],
            // Row 2: Sub-Header Level 2 (Sections)
            ['', '', '', '', '', '', '', 'OUTSTANDING', 'STOCK', 'PO', 'FORECAST', 'DELIVERY', 'OUTSTANDING', 'STOCK', 'PO', 'FORECAST', 'DELIVERY'],
            // Row 3: Sub-Header Level 3 (Metrics)
            ['', '', '', '', '', '', '', 'QTY', 'QTY', 'QTY', 'QTY', 'QTY', 'QTY', 'QTY', 'QTY', 'QTY', 'QTY'],
            // Row 4: Data Item 1
            ['C146', 'PT SUMBER AGUNG', 'PLANT 3', '1312006', 'MAIN BOARD A', '10.50', 'USD', '50', '100', '200', '210', '200', '40', '90', '300', '310', '300'],
            // Row 5: Data Item 2 (with IDR Currency)
            ['C147', 'PT KAWAI JAYA', 'PLANT 3', '1312008', 'SCREW B', '15000', 'IDR', '100', '500', '1000', '1200', '1000', '80', '450', '1100', '1250', '1100'],
        ];

        $parser = new ForecastTemplateParser();
        $result = $parser->parseTemplate($rawMatrix);

        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['parsed_rows']);
        $this->assertContains('2026-07', $result['periods']);
        $this->assertContains('2026-08', $result['periods']);

        $row1 = $result['parsed_rows'][0];
        $this->assertEquals('1312006', $row1['material_code']);
        $this->assertEquals('PT SUMBER AGUNG', $row1['supplier_name']);
        $this->assertEquals(10.50, $row1['unit_price']);
        $this->assertEquals('USD', $row1['currency']);
        $this->assertEquals(210, $row1['monthly_data']['2026-07']['forecast_qty']);
        $this->assertEquals(310, $row1['monthly_data']['2026-08']['forecast_qty']);
        $this->assertEquals(520, $row1['total_forecast_qty']);
    }

    public function test_forecast_template_reconciles_amount_formula()
    {
        $rawMatrix = [
            ['MATERIAL CODE', 'DESCRIPTION', 'UNIT PRICE', 'CURRENCY', 'JUL-26', ''],
            ['', '', '', '', 'FORECAST QTY', 'FORECAST AMOUNT'],
            ['1312006', 'KEYBOARD', '2.00', 'USD', '100', '200.00'],
        ];

        $parser = new ForecastTemplateParser();
        $result = $parser->parseTemplate($rawMatrix);

        $this->assertTrue($result['success']);
        $this->assertEquals(200.00, $result['parsed_rows'][0]['monthly_data']['2026-07']['forecast_amt']);
    }

    public function test_forecast_template_disambiguates_company_name_and_item_code()
    {
        $rawMatrix = [
            ['SUPPLIER CODE', 'SUPPLIER NAME', 'PLANT', 'MATERIAL CODE', 'DESCRIPTION', 'UNIT PRICE', 'CURRENCY', 'JUL-26'],
            ['', '', '', '', '', '', '', 'FORECAST QTY'],
            ['C025', 'CV. ASRI SEJAHTERA INDONESIA', 'KIP 1', 'CV. ASRI SEJAHTERA INDONESIA', 'BAG FOR SILICA', '2500', 'IDR', '500'],
        ];

        $parser = new ForecastTemplateParser();
        $result = $parser->parseTemplate($rawMatrix);

        $this->assertTrue($result['success']);
        $row = $result['parsed_rows'][0];
        $this->assertEquals('C025', $row['material_code']);
        $this->assertEquals('CV. ASRI SEJAHTERA INDONESIA', $row['supplier_name']);
        $this->assertEquals('BAG FOR SILICA', $row['description']);
    }
}
