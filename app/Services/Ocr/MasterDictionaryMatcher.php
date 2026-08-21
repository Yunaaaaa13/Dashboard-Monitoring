<?php

namespace App\Services\Ocr;

use App\Models\MasterPo;
use App\Models\PurchasingOutstanding;
use App\Models\PurchasingCategory;

/**
 * MasterDictionaryMatcher
 * 
 * Mesin pencocokan fuzzy berbasis Kamus Master Data untuk mendukung
 * koreksi cerdas pada hasil OCR scan dokumen purchasing PT Kawai Indonesia.
 */
class MasterDictionaryMatcher
{
    protected array $suppliers = [];
    protected array $materials = [];
    protected array $plants = ['Plant 3', 'Plant 1', 'Plant 2', 'Factory 3', 'Factory 1'];
    protected array $currencies = ['USD', 'IDR', 'JPY', 'EUR', 'SGD'];

    public function __construct()
    {
        $this->loadDictionaries();
    }

    protected function loadDictionaries(): void
    {
        try {
            $s1 = PurchasingOutstanding::pluck('supplier_name')->filter()->toArray();
            $s2 = MasterPo::pluck('supplier')->filter()->toArray();
            $this->suppliers = array_unique(array_filter(array_merge($s1, $s2)));

            $m1 = PurchasingOutstanding::pluck('part_number')->filter()->toArray();
            $m2 = PurchasingOutstanding::pluck('drawing')->filter()->toArray();
            $m3 = MasterPo::pluck('item_code')->filter()->toArray();
            $this->materials = array_unique(array_filter(array_merge($m1, $m2, $m3)));
        } catch (\Throwable $e) {
            $this->suppliers = [];
            $this->materials = [];
        }
    }

    /**
     * Cari kecocokan fuzzy terbaik untuk Nama Supplier
     * 
     * @param string $input
     * @param float $minSimilarity (0.0 s/d 1.0, default 0.70)
     * @return array [ 'is_exact' => bool, 'matched' => ?string, 'similarity' => float, 'suggested' => ?string ]
     */
    public function matchSupplier(string $input, float $minSimilarity = 0.70): array
    {
        $query = strtoupper(trim($input));
        if ($query === '') {
            return ['is_exact' => false, 'matched' => null, 'similarity' => 0.0, 'suggested' => null];
        }

        // 1. Exact Match
        foreach ($this->suppliers as $s) {
            if (strtoupper(trim($s)) === $query) {
                return ['is_exact' => true, 'matched' => $s, 'similarity' => 1.0, 'suggested' => $s];
            }
        }

        // 2. Fuzzy Match Levenshtein
        $bestMatch = null;
        $highestSim = 0.0;

        foreach ($this->suppliers as $s) {
            $target = strtoupper(trim($s));
            $lev = levenshtein($query, $target);
            $maxLen = max(strlen($query), strlen($target));
            $sim = $maxLen > 0 ? (1 - ($lev / $maxLen)) : 0.0;

            if ($sim > $highestSim) {
                $highestSim = $sim;
                $bestMatch = $s;
            }
        }

        if ($highestSim >= $minSimilarity && $bestMatch !== null) {
            return [
                'is_exact' => false,
                'matched' => $bestMatch,
                'similarity' => round($highestSim, 2),
                'suggested' => $bestMatch,
            ];
        }

        return ['is_exact' => false, 'matched' => null, 'similarity' => round($highestSim, 2), 'suggested' => null];
    }

    /**
     * Cari kecocokan fuzzy untuk Part Number / Material Code
    /**
     * Peta Karakter yang Sering Tertukar pada Scan OCR:
     * O <-> 0, I <-> 1, S <-> 5, B <-> 8, G <-> 6, Z <-> 2, D <-> 0, l <-> 1
     */
    protected array $ocrCharReplacements = [
        'O' => ['0'], '0' => ['O'],
        'I' => ['1', 'L'], '1' => ['I', 'L'], 'L' => ['1', 'I'],
        'S' => ['5'], '5' => ['S'],
        'B' => ['8'], '8' => ['B'],
        'G' => ['6'], '6' => ['G'],
        'Z' => ['2'], '2' => ['Z'],
        'D' => ['0'],
    ];

    /**
     * Cari kecocokan fuzzy untuk Part Number / Material Code
     * Menggunakan exact match, OCR character swap candidates, dan Levenshtein similarity.
     */
    public function matchMaterialCode(string $input, float $minSimilarity = 0.85): array
    {
        $query = strtoupper(trim($input));
        if ($query === '') {
            return ['is_exact' => false, 'matched' => null, 'similarity' => 0.0, 'suggested' => null, 'is_ocr_corrected' => false];
        }

        // 1. Exact Match
        foreach ($this->materials as $m) {
            if (strtoupper(trim($m)) === $query) {
                return ['is_exact' => true, 'matched' => $m, 'similarity' => 1.0, 'suggested' => $m, 'is_ocr_corrected' => false];
            }
        }

        // 2. OCR Character Candidate Match (Koreksi cerdas karakter tertukar O<->0, G<->6, dll)
        $candidates = $this->generateOcrCandidates($query);
        foreach ($candidates as $cand) {
            foreach ($this->materials as $m) {
                if (strtoupper(trim($m)) === $cand) {
                    return [
                        'is_exact' => false,
                        'matched' => $m,
                        'similarity' => 0.98,
                        'suggested' => $m,
                        'is_ocr_corrected' => true,
                    ];
                }
            }
        }

        // 3. Fuzzy Levenshtein Match
        $bestMatch = null;
        $highestSim = 0.0;

        foreach ($this->materials as $m) {
            $target = strtoupper(trim($m));
            $lev = levenshtein($query, $target);
            $maxLen = max(strlen($query), strlen($target));
            $sim = $maxLen > 0 ? (1 - ($lev / $maxLen)) : 0.0;

            if ($sim > $highestSim) {
                $highestSim = $sim;
                $bestMatch = $m;
            }
        }

        if ($highestSim >= $minSimilarity && $bestMatch !== null) {
            return [
                'is_exact' => false,
                'matched' => $bestMatch,
                'similarity' => round($highestSim, 2),
                'suggested' => $bestMatch,
                'is_ocr_corrected' => false,
            ];
        }

        return ['is_exact' => false, 'matched' => null, 'similarity' => round($highestSim, 2), 'suggested' => null, 'is_ocr_corrected' => false];
    }

    /**
     * Generate variasi string berdasarkan kamus karakter OCR
     */
    public function generateOcrCandidates(string $str): array
    {
        $candidates = [];
        $len = strlen($str);

        for ($i = 0; $i < $len; $i++) {
            $char = $str[$i];
            if (isset($this->ocrCharReplacements[$char])) {
                foreach ($this->ocrCharReplacements[$char] as $sub) {
                    $cand = substr($str, 0, $i) . $sub . substr($str, $i + 1);
                    $candidates[] = $cand;
                }
            }
        }

        return array_unique($candidates);
    }

    /**
     * Validasi dan Normalisasi Mata Uang
     */
    public function matchCurrency(string $input): array
    {
        $normalized = \App\Services\DataValidation\InputNormalizer::normalizeCurrency($input);
        return [
            'is_valid' => in_array($normalized, $this->currencies, true),
            'currency' => $normalized,
            'suggested' => $normalized,
        ];
    }
}
