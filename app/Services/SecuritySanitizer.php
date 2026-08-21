<?php

namespace App\Services;

class SecuritySanitizer
{
    /**
     * Prevent CSV/Excel Formula Injection (CSV Injection).
     * If a cell value starts with '=', '+', '-', '@', '\t', or '\r',
     * prefix it with a single quote so Excel parses it as plain text instead of executing formulas.
     */
    public static function sanitizeCell($value)
    {
        if ($value === null || is_numeric($value) || is_bool($value) || is_array($value)) {
            return $value;
        }

        $str = (string) $value;
        $trimmed = trim($str);

        if ($trimmed === '') {
            return $str;
        }

        $firstChar = substr($trimmed, 0, 1);
        if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"])) {
            // If it starts with formula operator but is a normal negative number (e.g. -123 or -12.5), allow it
            if ($firstChar === '-' && is_numeric($trimmed)) {
                return $value;
            }
            return "'" . $str;
        }

        return $str;
    }

    /**
     * Sanitize string inputs to prevent XSS / malicious injection.
     */
    public static function sanitizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = trim($value);
        $clean = strip_tags($clean);
        return htmlspecialchars($clean, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate uploaded document / image / Excel file properties.
     */
    public static function isAllowedFileType($file, array $allowedExtensions = ['xlsx', 'xls', 'csv', 'png', 'jpg', 'jpeg', 'pdf']): bool
    {
        if (!$file || !$file->isValid()) {
            return false;
        }

        $ext = strtolower($file->getClientOriginalExtension());
        return in_array($ext, $allowedExtensions, true);
    }
}
