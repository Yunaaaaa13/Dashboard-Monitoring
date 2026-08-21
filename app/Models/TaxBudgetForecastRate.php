<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxBudgetForecastRate extends Model
{
    use HasFactory;

    protected $table = 'tax_budget_forecast_rates';

    protected $fillable = [
        'exch_year',
        'exch_month',
        'currency_code',
        'budget_rate',
        'remarks',
        'last_update',
        'last_user',
        'user_id',
    ];

    protected $casts = [
        'exch_year'     => 'integer',
        'exch_month'    => 'integer',
        'currency_code' => 'integer',
        'budget_rate'   => 'integer',
        'last_update'   => 'date',
    ];

    public static array $currencyMap = [
        2 => ['label' => 'USD/IDR', 'symbol' => 'Rp', 'flag' => '🇺🇸'],
        1 => ['label' => 'JPY/IDR', 'symbol' => 'Rp', 'flag' => '🇯🇵'],
        3 => ['label' => 'EUR/IDR', 'symbol' => 'Rp', 'flag' => '🇪🇺'],
    ];

    public static array $monthNames = [
        1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
        4  => 'April',    5  => 'Mei',       6  => 'Juni',
        7  => 'Juli',     8  => 'Agustus',  9  => 'September',
        10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function scopeOfYear($query, int $year)
    {
        return $query->where('exch_year', $year);
    }

    public function scopeOfCurrency($query, int $code)
    {
        return $query->where('currency_code', $code);
    }

    public function getMonthNameAttribute(): string
    {
        return self::$monthNames[$this->exch_month] ?? "Bulan {$this->exch_month}";
    }

    public function getCurrencyLabelAttribute(): string
    {
        return self::$currencyMap[$this->currency_code]['label'] ?? "Code {$this->currency_code}";
    }

    public function getRateFormattedAttribute(): string
    {
        return number_format($this->budget_rate, 0, ',', '.');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
