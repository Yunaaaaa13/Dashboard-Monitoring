<?php

namespace App\Services\Normalization;

class ItemCodeNormalizer
{
    /**
     * Normalize an item code.
     * - Trims whitespace, control chars, BOM, NBSP
     * - Converts to uppercase
     * - KEEPS leading zeros (001234 stays 001234)
     * - Removes invisible Unicode characters
     * - Returns empty string if null/empty
     *
     * @param string|null $value
     * @return string
     */
    public static function normalize(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Remove ZERO WIDTH NO-BREAK SPACE (BOM)
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
        
        // Remove NBSP and other invisible characters (Unicode properties)
        $value = preg_replace('/[\p{Z}\p{C}]+/u', ' ', $value);
        
        // Trim standard whitespace and extra spaces
        $value = trim($value);
        
        // Convert to uppercase
        $value = strtoupper($value);

        return $value;
    }

    /**
     * Check if an item code is valid.
     * - Must be non-empty after trimming
     * - Must not be a company name (PT/CV)
     * - Must contain at least one alphanumeric character
     *
     * @param string|null $value
     * @return bool
     */
    public static function isValid(?string $value): bool
    {
        $normalized = self::normalize($value);
        
        if ($normalized === '') {
            return false;
        }

        // Not a company name like PT, CV, etc (simple check based on prefix/suffix if typical)
        // Usually item codes shouldn't start with PT. or CV.
        if (preg_match('/^(PT|CV|UD)\b\.?\s*/i', $normalized)) {
            return false;
        }

        // Must contain at least one alphanumeric character
        if (!preg_match('/[A-Z0-9]/', $normalized)) {
            return false;
        }

        return true;
    }

    /**
     * Compare two item codes.
     *
     * @param string|null $a
     * @param string|null $b
     * @return bool
     */
    public static function areSame(?string $a, ?string $b): bool
    {
        return self::normalize($a) === self::normalize($b);
    }
}
