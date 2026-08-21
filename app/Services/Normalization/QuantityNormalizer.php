<?php

namespace App\Services\Normalization;

class QuantityNormalizer
{
    /**
     * Normalize a quantity to a non-negative integer.
     * Handles Indonesian format (1.250) and international (1,250).
     * Returns 0 for null/empty/invalid.
     *
     * @param mixed $value
     * @return int
     */
    public static function normalize($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_int($value)) {
            return max(0, $value);
        }

        if (is_float($value)) {
            return max(0, (int) round($value));
        }

        $value = (string) $value;

        // Determine if it's using comma or dot as decimal separator
        // E.g., 1.250,50 (Indonesian) or 1,250.50 (International)
        $commaPos = strrpos($value, ',');
        $dotPos = strrpos($value, '.');

        if ($commaPos !== false && $dotPos !== false) {
            if ($commaPos > $dotPos) {
                // Indonesian: 1.250,50 or 57.570.790,20
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                // International: 1,250.50 or 57,570,790.20
                $value = str_replace(',', '', $value);
            }
        } elseif ($commaPos !== false) {
            // Only comma, could be 1,250 (thousands) or 12,5 (decimals)
            if (substr_count($value, ',') > 1 || preg_match('/,\d{3}$/', $value)) {
                $value = str_replace(',', '', $value);
            } else {
                $value = str_replace(',', '.', $value);
            }
        } elseif ($dotPos !== false) {
            // Only dot, could be 1.250 (thousands) or 12.5 (decimals)
            if (substr_count($value, '.') > 1 || preg_match('/\.\d{3}$/', $value)) {
                $value = str_replace('.', '', $value);
            }
        }

        // Clean up any remaining non-numeric characters (except dot and minus)
        $value = preg_replace('/[^\d.-]/', '', $value);

        if (!is_numeric($value)) {
            return 0;
        }

        return max(0, (int) round((float) $value));
    }

    /**
     * Normalize a value to a float with specified decimals.
     *
     * @param mixed $value
     * @param int $decimals
     * @return float
     */
    public static function normalizeFloat($value, int $decimals = 2): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_int($value) || is_float($value)) {
            return round((float) $value, $decimals);
        }

        $value = (string) $value;

        $commaPos = strrpos($value, ',');
        $dotPos = strrpos($value, '.');

        if ($commaPos !== false && $dotPos !== false) {
            if ($commaPos > $dotPos) {
                // Indonesian: 1.250,50 or 57.570.790,20
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                // International: 1,250.50 or 57,570,790.20
                $value = str_replace(',', '', $value);
            }
        } elseif ($commaPos !== false) {
            if (substr_count($value, ',') > 1 || preg_match('/,\d{3}$/', $value)) {
                $value = str_replace(',', '', $value);
            } else {
                $value = str_replace(',', '.', $value);
            }
        } elseif ($dotPos !== false) {
            if (substr_count($value, '.') > 1 || preg_match('/\.\d{3}$/', $value)) {
                $value = str_replace('.', '', $value);
            }
        }

        $value = preg_replace('/[^\d.-]/', '', $value);

        if (!is_numeric($value)) {
            return 0.0;
        }

        return round((float) $value, $decimals);
    }

    /**
     * Check if a value is valid.
     *
     * @param mixed $value
     * @return bool
     */
    public static function isValid($value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }
        
        if (is_int($value) || is_float($value)) {
            return true;
        }
        
        // Remove standard number formatting characters to check if it's purely numeric
        $cleaned = preg_replace('/[.,\s]/', '', (string)$value);
        return is_numeric($cleaned);
    }
}
