<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Normalisasi format periode agar konsisten menjadi format YYYY-MM (misal "JULY" / "2026-JULY" -> "2026-07")
     * sehingga dropdown hanya menampilkan salah satu standar dan tidak ada duplikat ("JULY" vs "2026-07").
     */
    protected function normalizePeriodString(?string $periode): string
    {
        if (empty($periode)) {
            return now()->format('Y-m');
        }
        $periode = trim($periode);
        if ($periode === 'All' || strcasecmp($periode, 'Semua Periode') === 0 || strcasecmp($periode, 'all') === 0) {
            return 'All';
        }
        if (preg_match('/^\d{4}-\d{2}$/', $periode)) {
            return $periode;
        }

        $monthsMap = [
            1  => ['JAN', 'JANUARY', '01', '1'],
            2  => ['FEB', 'FEBRUARY', '02', '2'],
            3  => ['MAR', 'MARCH', '03', '3'],
            4  => ['APR', 'APRIL', '04', '4'],
            5  => ['MAY', '05', '5'],
            6  => ['JUN', 'JUNE', '06', '6'],
            7  => ['JUL', 'JULY', '07', '7'],
            8  => ['AUG', 'AUGUST', '08', '8'],
            9  => ['SEP', 'SEPTEMBER', '09', '9'],
            10 => ['OCT', 'OCTOBER', '10'],
            11 => ['NOV', 'NOVEMBER', '11'],
            12 => ['DEC', 'DECEMBER', '12'],
        ];

        $year = now()->format('Y');
        $monthStr = strtoupper($periode);

        if (preg_match('/^(\d{4})[-_\s]+([A-Za-z]+)$/', $periode, $m)) {
            $year = $m[1];
            $monthStr = strtoupper($m[2]);
        } elseif (preg_match('/^([A-Za-z]+)[-_\s]+(\d{4})$/', $periode, $m)) {
            $monthStr = strtoupper($m[1]);
            $year = $m[2];
        }

        foreach ($monthsMap as $num => $names) {
            if (in_array($monthStr, $names, true)) {
                return $year . '-' . str_pad($num, 2, '0', STR_PAD_LEFT);
            }
        }

        return $periode;
    }

    /**
     * Menghasilkan array varian format periode untuk query database (misal "2026-07" -> ["2026-07", "JULY", "JUL", "07", "7"]).
     */
    protected function getPeriodVariantsString(string $periode): array
    {
        $variants = [trim($periode)];
        $upper = strtoupper(trim($periode));
        $variants[] = $upper;

        $monthsMap = [
            1  => ['JAN', 'JANUARY', '01', '1'],
            2  => ['FEB', 'FEBRUARY', '02', '2'],
            3  => ['MAR', 'MARCH', '03', '3'],
            4  => ['APR', 'APRIL', '04', '4'],
            5  => ['MAY', '05', '5'],
            6  => ['JUN', 'JUNE', '06', '6'],
            7  => ['JUL', 'JULY', '07', '7'],
            8  => ['AUG', 'AUGUST', '08', '8'],
            9  => ['SEP', 'SEPTEMBER', '09', '9'],
            10 => ['OCT', 'OCTOBER', '10'],
            11 => ['NOV', 'NOVEMBER', '11'],
            12 => ['DEC', 'DECEMBER', '12'],
        ];

        if (preg_match('/^(\d{4})-(\d{2})$/', $upper, $matches)) {
            $year  = $matches[1];
            $mNum  = (int) $matches[2];
            if (isset($monthsMap[$mNum])) {
                foreach ($monthsMap[$mNum] as $mName) {
                    $variants[] = $mName;
                    $variants[] = $year . '-' . $mName;
                    $variants[] = $mName . ' ' . $year;
                }
            }
        } else {
            foreach ($monthsMap as $num => $names) {
                if (in_array($upper, $names, true)) {
                    $mStr = str_pad($num, 2, '0', STR_PAD_LEFT);
                    $year = now()->format('Y');
                    $variants[] = $year . '-' . $mStr;
                    $variants[] = $mStr;
                    $variants[] = (string) $num;
                    foreach ($names as $n) {
                        $variants[] = $n;
                        $variants[] = $year . '-' . $n;
                    }
                    break;
                }
            }
        }

        return array_values(array_unique($variants));
    }
}
