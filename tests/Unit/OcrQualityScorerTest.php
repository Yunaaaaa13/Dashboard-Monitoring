<?php

namespace Tests\Unit;

use App\Services\Ocr\MasterDictionaryMatcher;
use App\Services\Ocr\OcrQualityScorer;
use Tests\TestCase;

class OcrQualityScorerTest extends TestCase
{
    /**
     * Test OCR Confidence Scoring & Character Swap Suggestion
     */
    public function test_ocr_scorer_numeric_typo_suggestion(): void
    {
        $matcher = new MasterDictionaryMatcher();

        // Baris dengan angka typo '2OO' (huruf O kapital)
        $row = [
            'material_code' => '1312006',
            'qty' => '2OO',
            'supplier' => 'PT. SERBAGUNA PRIMA'
        ];

        $res = OcrQualityScorer::scoreRow($row, $matcher);

        $this->assertEquals('MEDIUM_CONFIDENCE', $res['tier']);
        $this->assertTrue($res['needs_review']);
        $this->assertArrayHasKey('qty', $res['suggestions']);
        $this->assertEquals(200.0, $res['suggestions']['qty']);
    }

    /**
     * Test OCR High Confidence Clean Row
     */
    public function test_ocr_scorer_clean_row(): void
    {
        $matcher = new MasterDictionaryMatcher();

        $cleanRow = [
            'material_code' => '1312006',
            'qty' => 350,
            'supplier' => 'PT. KAWAI INDONESIA'
        ];

        $res = OcrQualityScorer::scoreRow($cleanRow, $matcher);

        $this->assertEquals('HIGH_CONFIDENCE', $res['tier']);
        $this->assertFalse($res['needs_review']);
        $this->assertEmpty($res['suggestions']);
    }
}
