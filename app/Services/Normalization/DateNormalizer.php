<?php

namespace App\Services\Normalization;

use Carbon\Carbon;
use Exception;

class DateNormalizer
{
    /**
     * Map Indonesian month names to English for parsing.
     *
     * @var array<string, string>
     */
    protected static array $indonesianMonths = [
        'januari'   => 'January',
        'februari'  => 'February',
        'maret'     => 'March',
        'april'     => 'April',
        'mei'       => 'May',
        'juni'      => 'June',
        'juli'      => 'July',
        'agustus'   => 'August',
        'september' => 'September',
        'oktober'   => 'October',
        'november'  => 'November',
        'desember'  => 'December',
        'jan'       => 'Jan',
        'feb'       => 'Feb',
        'mar'       => 'Mar',
        'apr'       => 'Apr',
        'jun'       => 'Jun',
        'jul'       => 'Jul',
        'agu'       => 'Aug',
        'sep'       => 'Sep',
        'okt'       => 'Oct',
        'nov'       => 'Nov',
        'des'       => 'Dec',
    ];

    /**
     * Convert various date formats to YYYY-MM-DD.
     *
     * @param string|null $value
     * @return string|null Returns YYYY-MM-DD or null if invalid
     */
    public static function toDate(?string $value): ?string
    {
        if (empty(trim((string)$value))) {
            return null;
        }

        $value = trim($value);

        // Handle Excel serial numbers (days since 1900-01-01)
        if (is_numeric($value) && $value > 10000 && $value < 100000) {
            try {
                // Excel dates usually start from Dec 30, 1899 due to 1900 leap year bug
                return Carbon::create(1899, 12, 30)->addDays((int)$value)->format('Y-m-d');
            } catch (Exception $e) {
                return null;
            }
        }

        // Translate Indonesian month names to English
        $lowerValue = strtolower($value);
        foreach (self::$indonesianMonths as $id => $en) {
            if (str_contains($lowerValue, $id)) {
                $value = str_ireplace($id, $en, $value);
                break;
            }
        }

        // Standardize separators
        $value = str_replace(['.', '/'], '-', $value);

        try {
            // Already YYYY-MM format
            if (preg_match('/^\d{4}-\d{2}$/', $value)) {
                return $value . '-01';
            }

            // Mon-YY (e.g. Jun-26)
            if (preg_match('/^[A-Za-z]{3}-\d{2}$/', $value)) {
                return Carbon::createFromFormat('M-y', $value)->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Convert various date formats to YYYY-MM period format.
     * Falls back to current month if invalid.
     *
     * @param string|null $value
     * @return string Returns YYYY-MM
     */
    public static function toPeriod(?string $value): string
    {
        $date = self::toDate($value);
        
        if ($date) {
            return substr($date, 0, 7);
        }
        
        return Carbon::now()->format('Y-m');
    }

    /**
     * Check if a value is a valid date.
     *
     * @param string|null $value
     * @return bool
     */
    public static function isValidDate(?string $value): bool
    {
        return self::toDate($value) !== null;
    }
}
