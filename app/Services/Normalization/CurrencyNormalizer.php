<?php

namespace App\Services\Normalization;

use App\Models\TaxBudgetForecastRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CurrencyNormalizer
{
    /**
     * Normalize currency code from raw text.
     *
     * @param string|null $rawCurrency
     * @param float|null $price
     * @param string|null $numberFormat
     * @return string ISO code: 'USD', 'IDR', 'JPY', 'EUR', 'SGD'
     */
    public static function detectCurrency(?string $rawCurrency, ?float $price = null, ?string $numberFormat = null): string
    {
        // Disambiguation: Price > 300 is consistently IDR (Rupiah) in this manufacturing context
        if ($price !== null && $price > 300) {
            return 'IDR';
        }

        if ($rawCurrency) {
            $rawCurrency = strtoupper(trim($rawCurrency));
            
            if (in_array($rawCurrency, ['IDR', 'RP', 'RUPIAH', 'RP.', 'IDR (RP)']) || str_contains($rawCurrency, 'RP') || str_contains($rawCurrency, 'RUPIAH')) return 'IDR';
            if (in_array($rawCurrency, ['USD', 'DOLLAR', '$', 'US DOLLAR', 'USD ($)'])) return 'USD';
            if (in_array($rawCurrency, ['JPY', 'YEN', '¥'])) return 'JPY';
            if (in_array($rawCurrency, ['EUR', 'EURO', '€'])) return 'EUR';
            if (in_array($rawCurrency, ['SGD'])) return 'SGD';
        }

        return 'USD';
    }

    /**
     * Convert amount to USD using TaxBudgetForecastRate.
     *
     * @param float $amount
     * @param string $fromCurrency
     * @param int|null $year
     * @param int|null $month
     * @return float
     */
    public static function convertToUsd(float $amount, string $fromCurrency, ?int $year = null, ?int $month = null): float
    {
        $fromCurrency = self::detectCurrency($fromCurrency);

        if ($fromCurrency === 'USD') {
            return $amount;
        }

        $rate = self::getExchangeRate($fromCurrency, 'USD', $year, $month);
        
        return $rate > 0 ? $amount * $rate : 0.0;
    }

    /**
     * Convert amount to IDR.
     *
     * @param float $amount
     * @param string $fromCurrency
     * @param int|null $year
     * @param int|null $month
     * @return float
     */
    public static function convertToIdr(float $amount, string $fromCurrency, ?int $year = null, ?int $month = null): float
    {
        $fromCurrency = self::detectCurrency($fromCurrency);

        if ($fromCurrency === 'IDR') {
            return $amount;
        }

        $rate = self::getExchangeRate($fromCurrency, 'IDR', $year, $month);
        
        return $rate > 0 ? $amount * $rate : 0.0;
    }

    /**
     * Get the exchange rate between two currencies.
     *
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param int|null $year
     * @param int|null $month
     * @return float
     */
    public static function getExchangeRate(string $fromCurrency, string $toCurrency = 'USD', ?int $year = null, ?int $month = null): float
    {
        $fromCurrency = self::detectCurrency($fromCurrency);
        $toCurrency = self::detectCurrency($toCurrency);

        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $now = Carbon::now();
        $year = $year ?? $now->year;
        $month = $month ?? $now->month;

        // DB Mapping: currency_code (2=USD/IDR, 1=JPY/IDR, 3=EUR/IDR)
        $codeMap = ['USD' => 2, 'JPY' => 1, 'EUR' => 3];

        if ($toCurrency === 'IDR' && isset($codeMap[$fromCurrency])) {
            $currencyCode = $codeMap[$fromCurrency];
            
            try {
                $rateModel = TaxBudgetForecastRate::where('exch_year', $year)
                    ->where('exch_month', $month)
                    ->where('currency_code', $currencyCode)
                    ->first();
                    
                if ($rateModel && $rateModel->budget_rate > 0) {
                    return (float) $rateModel->budget_rate;
                }
            } catch (\Exception $e) {
                Log::warning("Could not fetch rate for {$fromCurrency} to IDR: " . $e->getMessage());
            }

            // Fallback hardcoded rates to IDR
            $fallbacks = ['USD' => 16600, 'JPY' => 110, 'EUR' => 18043]; // 1 EUR = ~1.09 USD * 16600
            return $fallbacks[$fromCurrency] ?? 1.0;
        }
        
        if ($toCurrency === 'USD') {
            // First convert to IDR, then from IDR to USD
            if ($fromCurrency === 'IDR') {
                $usdToIdr = self::getExchangeRate('USD', 'IDR', $year, $month);
                return $usdToIdr > 0 ? 1 / $usdToIdr : 0.0;
            } else {
                $toIdr = self::getExchangeRate($fromCurrency, 'IDR', $year, $month);
                $usdToIdr = self::getExchangeRate('USD', 'IDR', $year, $month);
                return $usdToIdr > 0 ? $toIdr / $usdToIdr : 0.0;
            }
        }
        
        return 1.0;
    }
}
