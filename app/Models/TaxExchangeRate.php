<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxExchangeRate extends Model
{
    use HasFactory;

    protected $table = 'tax_exchange_rates';

    protected $fillable = [
        'exch_year',
        'exch_month',
        'week_code',
        'currency_code',
        'tax_exchange_rate',
        'start_date',
        'end_date',
        'last_update',
        'last_user',
        'register_date',
        'user_id',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'last_update'   => 'date',
        'register_date' => 'date',
        'exch_year'     => 'integer',
        'exch_month'    => 'integer',
        'week_code'     => 'integer',
        'currency_code' => 'integer',
        'tax_exchange_rate' => 'integer',
    ];

    /**
     * Map currency_code => label dan simbol
     */
    public static array $currencyMap = [
        2 => ['label' => 'USD/IDR', 'symbol' => 'Rp', 'flag' => '🇮🇩'],
        1 => ['label' => 'JPY/IDR', 'symbol' => 'Rp', 'flag' => '🇯🇵'],
        3 => ['label' => 'EUR/IDR', 'symbol' => 'Rp', 'flag' => '🇪🇺'],
    ];

    /**
     * Nama bulan dalam Bahasa Indonesia
     */
    public static array $monthNames = [
        1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
        4  => 'April',    5  => 'Mei',       6  => 'Juni',
        7  => 'Juli',     8  => 'Agustus',  9  => 'September',
        10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /** Scope: filter tahun tertentu */
    public function scopeOfYear($query, int $year)
    {
        return $query->where('exch_year', $year);
    }

    /** Scope: filter bulan tertentu */
    public function scopeOfMonth($query, int $month)
    {
        return $query->where('exch_month', $month);
    }

    /** Scope: filter currency_code */
    public function scopeOfCurrency($query, int $code)
    {
        return $query->where('currency_code', $code);
    }

    /** Label nama bulan */
    public function getMonthNameAttribute(): string
    {
        return self::$monthNames[$this->exch_month] ?? "Bulan {$this->exch_month}";
    }

    /** Label mata uang */
    public function getCurrencyLabelAttribute(): string
    {
        return self::$currencyMap[$this->currency_code]['label'] ?? "Code {$this->currency_code}";
    }

    /** Format kurs dengan titik pemisah ribuan */
    public function getRateFormattedAttribute(): string
    {
        return number_format($this->tax_exchange_rate, 0, ',', '.');
    }

    /** Relasi ke user */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
