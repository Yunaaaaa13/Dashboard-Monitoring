<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forecasting extends Model
{
    use HasFactory;

    protected $table = 'forecastings';

    protected $fillable = [
        'part_number', 'factory_code', 'description', 'supplier_name', 'periode', 'user_id', 'price', 'currency',
        'forecast_qty', 'outstanding_pre', 'stock_pre', 'delivery', 'outstanding',
        // Legacy fields (dipertahankan agar fitur lama tidak rusak)
        'po', 'production', 'stock', 'actual',
        'period_month', 'po_qty', 'production_qty', 'stock_qty', 'actual_qty',
        'delivery_category_code',
    ];

    public function getCurrencySymbolAttribute(): string
    {
        $curr = strtoupper(trim($this->currency ?? ($this->deliveryCategory?->currency ?? 'USD')));
        return $curr === 'IDR' ? 'Rp ' : '$ ';
    }

    public function getDeliveryCategoryBadgeAttribute(): string
    {
        $code = strtoupper(trim($this->delivery_category_code ?? 'LOC'));
        return match ($code) {
            'IMP', 'IMPORT'     => '<span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 px-2 py-1"><i class="bi bi-plane-fill me-1"></i>IMP (Impor)</span>',
            'CON', 'CONSUMABLE' => '<span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-2 py-1"><i class="bi bi-box-seam me-1"></i>CON (Consumable)</span>',
            default             => '<span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2 py-1"><i class="bi bi-geo-alt-fill me-1"></i>LOC (Lokal)</span>',
        };
    }

    /**
     * Get price with fallback to PurchasingOutstanding if not explicitly set.
     */
    public function getPriceAttribute($value): float
    {
        if ($value !== null && (float) $value > 0) {
            return (float) $value;
        }
        $po = \App\Models\PurchasingOutstanding::where('part_number', $this->part_number)
            ->orWhere('drawing', $this->part_number)
            ->where('price', '>', 0)
            ->first();
        if ($po) {
            return (float) $po->price;
        }
        return 0.0;
    }

    /**
     * Hitung Outstanding Amount = Outstanding QTY * Price
     */
    public function getCalculatedOutstandingAmountAttribute(): float
    {
        return (float) ($this->calculated_outstanding * $this->price);
    }

    /**
     * Hitung Stock Amount = Stock QTY * Price
     */
    public function getCalculatedStockAmountAttribute(): float
    {
        return (float) ($this->calculated_stock * $this->price);
    }

    /**
     * Hitung PO Amount = PO QTY * Price
     */
    public function getCalculatedPoAmountAttribute(): float
    {
        return (float) ($this->calculated_po * $this->price);
    }

    /**
     * Format Amount dengan standar Excel:
     * - Jika min (-), menggunakan tanda kurung: $ (1.564,77)
     * - Jika 0: $ -
     * - Jika positif: $ 99.359,71
     */
    public function formatAmount($amount, $currencySymbol = null): string
    {
        if ($currencySymbol === null) {
            $currencySymbol = $this->currency_symbol;
        }
        if ($amount === null) return '-';
        $val = (float) $amount;
        if (abs($val) < 0.0001) return $currencySymbol . '-';
        if ($val < 0) {
            return $currencySymbol . '(' . number_format(abs($val), 2, ',', '.') . ')';
        }
        return $currencySymbol . number_format($val, 2, ',', '.');
    }

    /**
     * Hitung PO secara dinamis dari Step 2 (Master PO).
     * Jika belum di-input di Step 2, maka nilainya otomatis 0 / NULL.
     */
    public function getCalculatedPoAttribute(): int
    {
        $poSum = \App\Models\MasterPo::where('item_code', $this->part_number)
            ->orWhere('po', $this->part_number)
            ->sum('qty');

        if ($poSum > 0) {
            return (int) $poSum;
        }

        // Murni bernilai 0 jika pengguna belum membuat/mengisi Master PO (Step 2)
        return 0;
    }

    /**
     * Hitung Delivery secara dinamis murni dari Step 3 (Realisasi Penerimaan / PurchasingLog).
     * Jika belum ada realisasi di Step 3, nilainya murni 0.
     */
    public function getCalculatedDeliveryAttribute(): int
    {
        $logSum = \App\Models\PurchasingLog::where('item_code', $this->part_number)
            ->orWhere('po_reference', $this->part_number)
            ->sum('actual_received');

        if ($logSum > 0) {
            return (int) $logSum;
        }

        $del = \App\Models\ForecastActual::where('part_number', $this->part_number)
            ->where('periode', $this->periode)
            ->sum('forecast_actual');

        if ($del <= 0) {
            $del = \App\Models\Actual::where('part_number', $this->part_number)
                ->where(function ($q) {
                    $q->where('periode', $this->periode)->orWhere('period_month', $this->periode);
                })
                ->sum('actual_qty');
        }

        if ($del > 0) {
            return (int) $del;
        }

        // Murni bernilai 0 jika pengguna belum melakukan realisasi penerimaan di Step 3
        return 0;
    }

    /**
     * Rumus Excel Forecast: Forecast = PO - Outstanding (pre month)
     */
    public function getCalculatedForecastAttribute(): int
    {
        $rawForecast = (int) ($this->attributes['forecast_qty'] ?? 0);
        if ($rawForecast > 0) {
            return $rawForecast;
        }
        $po = $this->calculated_po;
        $outPre = (int) ($this->attributes['outstanding_pre'] ?? 0);
        return max(0, $po - $outPre);
    }

    /**
     * Rumus Excel Outstanding: Outstanding = Outstanding (pre month) + PO - Delivery
     */
    public function getCalculatedOutstandingAttribute(): int
    {
        $outPre = (int) ($this->attributes['outstanding_pre'] ?? 0);
        $po     = $this->calculated_po;
        $del    = $this->calculated_delivery;
        return $outPre + $po - $del;
    }

    /**
     * Rumus Excel Stock Akhir: Stock = Stock (pre month) + Delivery - PROD
     */
    public function getCalculatedStockAttribute(): int
    {
        $stockPre = (int) ($this->attributes['stock_pre'] ?? ($this->attributes['stock_qty'] ?? 0));
        $del      = $this->calculated_delivery;
        $prod     = (int) ($this->attributes['production_qty'] ?? ($this->attributes['production'] ?? 0));
        return $stockPre + $del - $prod;
    }

    /**
     * Accessor: forecast_qty → fallback ke calculated_forecast atau stock_qty.
     */
    public function getForecastQtyAttribute($value)
    {
        if ($value !== null && (int)$value > 0) {
            return (int)$value;
        }
        $po = $this->calculated_po;
        $outPre = (int) ($this->attributes['outstanding_pre'] ?? 0);
        $calc = max(0, $po - $outPre);
        if ($calc > 0) {
            return $calc;
        }
        return (int)($this->attributes['stock_qty'] ?? ($this->attributes['po_qty'] ?? 0));
    }

    protected static function booted()
    {
        static::saving(function ($model) {
            // Sinkronisasi periode ↔ period_month
            if ($model->periode && !$model->period_month) {
                $model->period_month = $model->periode;
            }
            if ($model->period_month && !$model->periode) {
                $model->periode = $model->period_month;
            }

            // Sinkronisasi kolom legacy po ↔ po_qty
            if ($model->po !== null && $model->po_qty === null) {
                $model->po_qty = $model->po;
            }
            if ($model->po_qty !== null && $model->po === null) {
                $model->po = $model->po_qty;
            }

            // Sinkronisasi production ↔ production_qty
            if ($model->production !== null && $model->production_qty === null) {
                $model->production_qty = $model->production;
            }
            if ($model->production_qty !== null && $model->production === null) {
                $model->production = $model->production_qty;
            }

            // Sinkronisasi stock ↔ stock_qty
            if ($model->stock !== null && $model->stock_qty === null) {
                $model->stock_qty = $model->stock;
            }
            if ($model->stock_qty !== null && $model->stock === null) {
                $model->stock = $model->stock_qty;
            }

            // Sinkronisasi actual (legacy) ↔ actual_qty (legacy)
            if ($model->actual !== null && $model->actual_qty === null) {
                $model->actual_qty = $model->actual;
            }
            if ($model->actual_qty !== null && $model->actual === null) {
                $model->actual = $model->actual_qty;
            }
        });
    }
}
