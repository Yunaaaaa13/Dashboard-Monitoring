<?php

namespace App\Services\DataValidation;

/**
 * InputNormalizer
 * 
 * Standar normalisasi data terpadu untuk sistem purchasing PT Kawai Indonesia.
 * Bertanggung jawab membersihkan, merapikan format teks, mempertahankan leading zero,
 * dan menormalisasi angka numerik lintas locale (Indonesia & Internasional).
 */
class InputNormalizer
{
    /**
     * Normalisasi Part Number / Material Code
     * - Melakukan trim spasi di awal dan akhir
     * - Mengubah ke uppercase
     * - Mempertahankan leading zeroes (misal: '00123' tetap '00123')
     * - Menghilangkan spasi tak terlihat (non-breaking spaces, control characters)
     */
    public static function normalizeMaterialCode(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        // Hapus UTF-8 BOM dan non-breaking spaces (\u{00A0})
        $cleaned = preg_replace('/[\x{00A0}\x{200B}\x{FEFF}]/u', ' ', (string) $value);
        $cleaned = trim($cleaned);
        $cleaned = strtoupper($cleaned);

        return $cleaned;
    }

    /**
     * Normalisasi Nama Supplier / Vendor
     * - Trim spasi berlebih
     * - Standardisasi awalan perusahaan (PT., PT, CV., CV, etc.)
     * - Case normalization yang konsisten
     */
    public static function normalizeSupplierName(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $cleaned = preg_replace('/[\x{00A0}\x{200B}\x{FEFF}]/u', ' ', (string) $value);
        $cleaned = trim(preg_replace('/\s+/', ' ', $cleaned));

        // Standardisasi PT / CV (bisa dengan atau tanpa spasi setelah titik, misal "PT.DUNIA" -> "PT. DUNIA")
        $cleaned = preg_replace('/^P\.?\s*T\.?\s*/i', 'PT. ', $cleaned);
        $cleaned = preg_replace('/^C\.?\s*V\.?\s*/i', 'CV. ', $cleaned);

        return strtoupper(trim($cleaned));
    }

    /**
     * Mengembalikan daftar variasi penulisan nama supplier (misal: "PT. DUNIA KAYU JAYA" & "PT.DUNIA KAYU JAYA")
     * untuk query SQL yang toleran terhadap perbedaan spasi di database.
     */
    public static function getSupplierVariations(?string $value): array
    {
        if (empty($value) || $value === 'All' || $value === 'ALL') {
            return [];
        }

        $normalized = self::normalizeSupplierName($value);
        $raw        = strtoupper(trim((string)$value));
        $noSpacePt  = preg_replace('/^PT\.\s+/i', 'PT.', $normalized);
        $noSpaceCv  = preg_replace('/^CV\.\s+/i', 'CV.', $normalized);

        return array_values(array_unique(array_filter([$raw, $normalized, $noSpacePt, $noSpaceCv])));
    }

    /**
     * Normalisasi Nilai Numerik (Quantity, Price, Amount)
     * - Menangani format Indonesia (1.250,50) dan Internasional (1,250.50)
     * - Menghapus simbol mata uang ($ / Rp / USD / IDR)
     * - Menolak karakter alfabet yang bukan angka (misal '2OO' -> dibersihkan atau ditandai)
     */
    public static function normalizeNumber($value, int $decimals = 2, bool $allowNegative = true): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $num = (float) $value;
            return (!$allowNegative && $num < 0) ? 0.0 : round($num, $decimals);
        }

        $rawStr = (string) $value;
        $isIndonesianPrefix = (bool) preg_match('/^\s*(Rp|IDR)\s*/i', $rawStr);
        
        $str = preg_replace('/[\x{00A0}\x{200B}\x{FEFF}]/u', '', $rawStr);
        // Hapus simbol mata uang dan teks mata uang di awal/akhir/tengah
        $str = preg_replace('/(USD|IDR|JPY|EUR|SGD|RP|RUPIAH|DOLLAR|DOLAR)/i', '', $str);
        $str = preg_replace('/[Rp\$€¥\s]/i', '', $str);
        $str = trim($str);

        if ($str === '') {
            return null;
        }

        // Deteksi format pemisah ribuan dan desimal:
        $lastComma = strrpos($str, ',');
        $lastDot = strrpos($str, '.');

        if ($lastComma !== false && $lastDot !== false) {
            if ($lastComma > $lastDot) {
                // Format Indonesia / Eropa: 1.250,50 -> ganti titik dengan kosong, koma dengan titik
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                // Format US / UK: 1,250.50 -> ganti koma dengan kosong
                $str = str_replace(',', '', $str);
            }
        } elseif ($lastComma !== false) {
            // Hanya ada koma: misal '1,25' (desimal) atau '1,250' (ribuan)
            $parts = explode(',', $str);
            if (count($parts) === 2 && strlen($parts[1]) <= 2) {
                // Desimal (misal 12,5 atau 12,50)
                $str = str_replace(',', '.', $str);
            } else {
                // Ribuan (misal 1,000 atau 10,000)
                $str = str_replace(',', '', $str);
            }
        } elseif ($lastDot !== false) {
            // Jika ada lebih dari 1 titik, berarti pemisah ribuan (1.000.000)
            if (substr_count($str, '.') > 1) {
                $str = str_replace('.', '', $str);
            } elseif ($isIndonesianPrefix || preg_match('/\.\d{3}$/', $str)) {
                // Misal '1.250' atau 'Rp 50.000' -> titik adalah pemisah ribuan
                $str = str_replace('.', '', $str);
            }
        }

        if (!is_numeric($str)) {
            return null;
        }

        $val = (float) $str;
        if (!$allowNegative && $val < 0) {
            $val = 0.0;
        }

        return round($val, $decimals);
    }

    /**
     * Normalisasi Kode Mata Uang
     */
    public static function normalizeCurrency(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return 'USD';
        }

        $cleaned = strtoupper(trim($value));
        $cleaned = preg_replace('/[^A-Z]/', '', $cleaned);

        if (in_array($cleaned, ['USD', 'IDR', 'JPY', 'EUR', 'SGD', 'CNY'], true)) {
            return $cleaned;
        }

        if ($cleaned === 'RP' || $cleaned === 'RUPIAH') {
            return 'IDR';
        }

        if ($cleaned === 'DOLLAR' || $cleaned === 'DOLAR') {
            return 'USD';
        }

        return 'USD';
    }

    /**
     * Normalisasi Tanggal ke Format YYYY-MM-DD
     */
    public static function normalizeDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $val = trim($value);

        // Jika numeric excel timestamp (misal: 45150)
        if (is_numeric($val) && (int) $val > 30000 && (int) $val < 60000) {
            $unixTime = ($val - 25569) * 86400;
            return gmdate('Y-m-d', (int) $unixTime);
        }

        // Standard ISO YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
            return $val;
        }

        // Format DD/MM/YYYY atau DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})$/', $val, $matches)) {
            $d = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $m = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $y = $matches[3];
            return "{$y}-{$m}-{$d}";
        }

        // Format YYYY/MM/DD
        if (preg_match('/^(\d{4})[\/\-\.](\d{1,2})[\/\-\.](\d{1,2})$/', $val, $matches)) {
            $y = $matches[1];
            $m = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $d = str_pad($matches[3], 2, '0', STR_PAD_LEFT);
            return "{$y}-{$m}-{$d}";
        }

        $timestamp = strtotime($val);
        if ($timestamp !== false && $timestamp > 0) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    /**
     * Normalisasi Factory / Plant Code
     */
    public static function normalizeFactoryCode(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return 'Plant 3';
        }

        $val = trim($value);
        if (preg_match('/plant\s*3/i', $val) || $val === '3' || preg_match('/p3/i', $val)) {
            return 'Plant 3';
        }
        if (preg_match('/plant\s*1/i', $val) || $val === '1' || preg_match('/p1/i', $val)) {
            return 'Plant 1';
        }
        if (preg_match('/plant\s*2/i', $val) || $val === '2' || preg_match('/p2/i', $val)) {
            return 'Plant 2';
        }

        return ucwords(strtolower($val));
    }

    /**
     * Alias untuk membersihkan Material Code
     */
    public static function cleanMaterialCode(?string $value): string
    {
        return self::normalizeMaterialCode($value);
    }

    /**
     * Membersihkan nilai kuantitas menjadi integer bulat non-negatif
     */
    public static function cleanQuantity($value): int
    {
        if ($value === null || $value === '') return 0;
        if (is_numeric($value)) return max(0, (int) round((float)$value));
        $cleaned = preg_replace('/[^0-9]/', '', (string)$value);
        return empty($cleaned) ? 0 : (int)$cleaned;
    }

    /**
     * Normalisasi Harga Satuan berdasarkan Mata Uang & Magnitudo
     */
    public static function normalizePrice($value, string $currency = 'USD'): float
    {
        if ($value === null || $value === '') return 0.0;
        $num = self::normalizeNumber($value, 4, false);
        return $num !== null ? max(0.0, $num) : 0.0;
    }

    /**
     * Normalisasi Kode Plant / Pabrik
     */
    public static function normalizePlantCode(?string $value): string
    {
        return self::normalizeFactoryCode($value);
    }

    /**
     * Normalisasi Periode Menjadi Format Kanonikal YYYY-MM
     * Contoh: '2026-08-15' -> '2026-08', '03-Aug-26' -> '2026-08', 'August 2026' -> '2026-08'
     */
    public static function canonicalPeriod($value): string
    {
        if ($value === null || trim((string)$value) === '' || strtoupper(trim((string)$value)) === 'ALL') {
            return date('Y-m');
        }

        $val = trim((string)$value);

        // Jika sudah YYYY-MM
        if (preg_match('/^(\d{4})-(\d{2})$/', $val, $m)) {
            return "{$m[1]}-{$m[2]}";
        }

        // Jika YYYY-MM-DD
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $val, $m)) {
            return "{$m[1]}-{$m[2]}";
        }

        // Jika DD/MM/YYYY atau DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/', $val, $m)) {
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            return "{$m[3]}-{$month}";
        }

        // Jika Excel serial timestamp (45000 - 55000)
        if (is_numeric($val) && (int)$val > 30000 && (int)$val < 60000) {
            $unixTime = ($val - 25569) * 86400;
            return gmdate('Y-m', (int)$unixTime);
        }

        // Parsing via strtotime
        $timestamp = strtotime($val);
        if ($timestamp !== false && $timestamp > 0) {
            return date('Y-m', $timestamp);
        }

        return date('Y-m');
    }

    /**
     * Normalisasi Nomor PO
     */
    public static function canonicalPoNumber(?string $value): string
    {
        if ($value === null) return '';
        $cleaned = trim(preg_replace('/[\x{00A0}\x{200B}\x{FEFF}]/u', ' ', (string)$value));
        $cleaned = strtoupper(preg_replace('/\s+/', ' ', $cleaned));
        return $cleaned;
    }

    /**
     * Mengambil Basis Nomor PO tanpa Suffix Tahun/Bulan
     * Misal: 'KI-TJT-0023/2026' -> 'KI-TJT-0023', 'KI-TJT-0023-2026' -> 'KI-TJT-0023'
     */
    public static function basePoNumber(?string $value): string
    {
        $po = self::canonicalPoNumber($value);
        if ($po === '') return '';
        // Hapus suffix tahun seperti /2026, /26, -2026
        $base = preg_replace('/[\/\-](20\d{2}|\d{2})$/', '', $po);
        return trim($base);
    }

    /**
     * Memeriksa apakah teks merupakan nama entitas perusahaan (PT/CV/UD/Tbk)
     */
    public static function isCompanyName(?string $value): bool
    {
        if ($value === null || trim($value) === '') return false;
        $upper = strtoupper(trim($value));
        return (bool) preg_match('/^(PT|CV|UD|PD|TBK|TOKO|YAYASAN)[\s\.]|\b(INDONESIA|SEJAHTERA|MAKMUR|ABADI|TEKNIK|JAYA|PERSADA|KARYA|PLASTIK|STEEL|LOGISTIK|GLOBAL|UTAMA|SENTOSA|BIMASAKTI|MITRA|TRADING)\b/i', $upper);
    }
}

