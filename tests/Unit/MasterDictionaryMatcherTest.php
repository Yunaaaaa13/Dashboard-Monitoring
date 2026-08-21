<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Ocr\MasterDictionaryMatcher;

class MasterDictionaryMatcherTest extends TestCase
{
    public function test_ocr_candidate_generator_creates_character_swap_variations()
    {
        $matcher = new MasterDictionaryMatcher();
        $candidates = $matcher->generateOcrCandidates('131200G');

        $this->assertContains('1312006', $candidates);
    }

    public function test_currency_matching_normalizes_rupiah_and_dollar()
    {
        $matcher = new MasterDictionaryMatcher();

        $idrResult = $matcher->matchCurrency('Rp');
        $this->assertEquals('IDR', $idrResult['currency']);

        $usdResult = $matcher->matchCurrency('Dollar');
        $this->assertEquals('USD', $usdResult['currency']);
    }
}
