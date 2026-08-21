<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasingOutstanding extends Model
{
    use HasFactory;

    protected $table = 'purchasing_outstandings';

    protected $fillable = [
        'po_number',
        'po_date',
        'part_number',
        'factory_code',
        'description',
        'category_id',
        'user_id',
        'order_qty',
        'drawing',
        'price',
        'currency',
        'price_deviation_reason',
        'amount',
        'complete',
        'status',
        'workflow_stage',
        'approval_notes',
        'supplier_name',
        'pic_buyer',
        'eta_date',
        'plan_stock',
        'plan_outstand',
        'm0_po',
        'm0_prod',
        'm1_po',
        'm1_prod',
        'm2_po',
        'm2_prod',
        'm3_po',
        'm3_prod',
        'm4_po',
        'm4_prod',
        'm5_po',
        'm5_prod',
        'm6_po',
        'm6_prod',
        'm7_po',
        'm7_prod',
        'm8_po',
        'm8_prod',
        'm9_po',
        'm9_prod',
        'm10_po',
        'm10_prod',
        'm11_po',
        'm11_prod',
        'm12_po',
        'm12_prod',
        'm13_po', 'm13_prod',
        'm14_po', 'm14_prod',
        'm15_po', 'm15_prod',
        'm16_po', 'm16_prod',
        'm17_po', 'm17_prod',
        'm18_po', 'm18_prod',
        'm19_po', 'm19_prod',
        'm20_po', 'm20_prod',
        'm21_po', 'm21_prod',
        'm22_po', 'm22_prod',
        'm23_po', 'm23_prod',
        'm24_po', 'm24_prod',
        'm25_po', 'm25_prod',
        'm26_po', 'm26_prod',
        'm27_po', 'm27_prod',
        'm28_po', 'm28_prod',
        'm29_po', 'm29_prod',
        'm30_po', 'm30_prod',
        'm31_po', 'm31_prod',
        'm32_po', 'm32_prod',
        'm33_po', 'm33_prod',
        'm34_po', 'm34_prod',
        'm35_po', 'm35_prod',
        'm36_po', 'm36_prod',
        'm0_inventory',
        'm1_inventory', 'm2_inventory', 'm3_inventory', 'm4_inventory', 'm5_inventory', 'm6_inventory',
        'm7_inventory', 'm8_inventory', 'm9_inventory', 'm10_inventory', 'm11_inventory', 'm12_inventory',
        'm13_inventory', 'm14_inventory', 'm15_inventory', 'm16_inventory', 'm17_inventory', 'm18_inventory',
        'm19_inventory', 'm20_inventory', 'm21_inventory', 'm22_inventory', 'm23_inventory', 'm24_inventory',
        'm25_inventory', 'm26_inventory', 'm27_inventory', 'm28_inventory', 'm29_inventory', 'm30_inventory',
        'm31_inventory', 'm32_inventory', 'm33_inventory', 'm34_inventory', 'm35_inventory', 'm36_inventory',
        'delivery_category_code',
    ];

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
     * Accessor untuk Price dengan fallback ke Forecasting jika price di table 0.
     */
    public function getPriceAttribute($value): float
    {
        if ($value !== null && (float) $value > 0) {
            return (float) $value;
        }
        $part = $this->part_number ?: $this->drawing;
        if ($part) {
            $fc = \App\Models\Forecasting::where('part_number', $part)->where('price', '>', 0)->first();
            if ($fc) {
                return (float) $fc->price;
            }
        }
        return (float) ($value ?? 0);
    }

    /**
     * Hitung Outstanding Amount Pre-month (Month 0: DEC) = plan_outstand * price
     */
    public function getPlanOutstandAmountAttribute(): float
    {
        return (float) (($this->plan_outstand ?? 0) * $this->price);
    }

    /**
     * Hitung total Target Order Qty PO.
     */
    public function getComputedOrderQtyAttribute(): int
    {
        if ($this->order_qty > 0) {
            return (int)$this->order_qty;
        }
        $sumPo = 0;
        for ($i = 1; $i <= 36; $i++) {
            $sumPo += (int)($this->{"m{$i}_po"} ?? 0);
        }
        if ($sumPo > 0) {
            return $sumPo;
        }
        $part = $this->part_number ?: $this->drawing;
        if ($part) {
            $mPoSum = \App\Models\MasterPo::where('item_code', $part)->orWhere('po', $part)->sum('qty');
            if ($mPoSum > 0) {
                return (int)$mPoSum;
            }
        }
        return 0;
    }

    /**
     * Hitung total Amount ($) = Computed Order Qty * Price.
     */
    public function getComputedAmountAttribute(): float
    {
        $priceVal = (float) $this->price;
        return (float)($this->computed_order_qty * $priceVal);
    }

    /**
     * Hitung Plan Stock Amount Pre-month (Month 0: DEC) = plan_stock * price
     */
    public function getPlanStockAmountAttribute(): float
    {
        return (float) (($this->plan_stock ?? 0) * $this->price);
    }

    public function getCurrencySymbolAttribute(): string
    {
        $curr = strtoupper(trim($this->currency ?? ($this->deliveryCategory?->currency ?? 'USD')));
        return $curr === 'IDR' ? 'Rp ' : '$ ';
    }

    /**
     * Helper Format Amount dengan standar Excel:
     * - Jika min (-): $ (3.069,80)
     * - Jika 0: $ -
     * - Jika positif: $ 99.359,71
     */
    public function formatAmount($amount = null, $currencySymbol = null): string
    {
        if ($amount === null) {
            $amount = $this->computed_amount ?: ($this->amount ?? 0);
        }
        if ($currencySymbol === null) {
            $currencySymbol = $this->currency_symbol;
        }
        $val = (float) $amount;
        if (abs($val) < 0.0001) return $currencySymbol . '-';
        if ($val < 0) {
            return $currencySymbol . '(' . number_format(abs($val), 2, ',', '.') . ')';
        }
        return $currencySymbol . number_format($val, 2, ',', '.');
    }

    /** Kategori material untuk pengelompokan dashboard dan laporan. */
    public function category()
    {
        return $this->belongsTo(PurchasingCategory::class, 'category_id');
    }

    /** User / PIC Buyer yang menangani item ini. */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static ?array $masterPoCache = null;
    protected static ?array $deliveryLogCache = null;
    protected static ?array $forecastingCache = null;

    public static function clearCalcCaches(): void
    {
        static::$masterPoCache = null;
        static::$deliveryLogCache = null;
        static::$forecastingCache = null;
    }

    public function getPeriodForMonth(int $index): string
    {
        $startMonth = session('monitor_start_month', 'JUN');
        $startYear  = (int) session('monitor_year', session('monitor_start_year', 2026));

        $allMonths = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        $monthNums = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

        $searchM = strtoupper(trim($startMonth));
        if ($searchM === 'JULY') $searchM = 'JUL';

        $startIndex = array_search($searchM, $allMonths);
        if ($startIndex === false) {
            $startIndex = 5; // JUN default
        }

        $totalMonthsFromStart = $startIndex + $index;
        $yearOffset = (int) floor($totalMonthsFromStart / 12);
        $monthIndex = $totalMonthsFromStart % 12;

        $year = $startYear + $yearOffset;
        $monthStr = $monthNums[$monthIndex];

        return sprintf('%04d-%s', $year, $monthStr);
    }

    private function initMasterPoCache(): void
    {
        if (static::$masterPoCache !== null) return;

        static::$masterPoCache = [];
        $pos = \Illuminate\Support\Facades\DB::table('master_pos')->select('item_code', 'po', 'tanggal', 'qty')->get();

        foreach ($pos as $p) {
            $code   = strtoupper(trim($p->item_code ?? ''));
            $poCode = strtoupper(trim($p->po ?? ''));
            $t      = trim($p->tanggal ?? '');
            if (empty($t)) continue;

            $ts     = strtotime($t);
            $period = date('Y-m', $ts);
            $mNum   = date('m', $ts);
            $qty    = (int) $p->qty;

            if ($code) {
                static::$masterPoCache[$code][$period] = (static::$masterPoCache[$code][$period] ?? 0) + $qty;
                static::$masterPoCache[$code]['m_' . $mNum] = (static::$masterPoCache[$code]['m_' . $mNum] ?? 0) + $qty;
            }
            if ($poCode && $poCode !== $code) {
                static::$masterPoCache[$poCode][$period] = (static::$masterPoCache[$poCode][$period] ?? 0) + $qty;
                static::$masterPoCache[$poCode]['m_' . $mNum] = (static::$masterPoCache[$poCode]['m_' . $mNum] ?? 0) + $qty;
            }
        }
    }

    private function initDeliveryLogCache(): void
    {
        if (static::$deliveryLogCache !== null) return;

        static::$deliveryLogCache = [];

        // 1. PurchasingLog
        $logs = \Illuminate\Support\Facades\DB::table('purchasing_logs')->select('item_code', 'po_reference', 'period_month', 'receipt_date', 'actual_received')->get();
        foreach ($logs as $l) {
            $code  = strtoupper(trim($l->item_code ?? ''));
            $poRef = strtoupper(trim($l->po_reference ?? ''));
            $pStr  = trim($l->period_month ?? $l->receipt_date ?? '');
            if (empty($pStr)) continue;

            $ts     = strtotime($pStr);
            $period = strlen($pStr) === 7 ? $pStr : date('Y-m', $ts);
            $mNum   = date('m', $ts);
            $qty    = (int) $l->actual_received;

            if ($code) {
                static::$deliveryLogCache[$code][$period] = (static::$deliveryLogCache[$code][$period] ?? 0) + $qty;
                static::$deliveryLogCache[$code]['m_' . $mNum] = (static::$deliveryLogCache[$code]['m_' . $mNum] ?? 0) + $qty;
            }
            if ($poRef && $poRef !== $code) {
                static::$deliveryLogCache[$poRef][$period] = (static::$deliveryLogCache[$poRef][$period] ?? 0) + $qty;
                static::$deliveryLogCache[$poRef]['m_' . $mNum] = (static::$deliveryLogCache[$poRef]['m_' . $mNum] ?? 0) + $qty;
            }
        }

        // 2. ForecastActual
        try {
            $faList = \Illuminate\Support\Facades\DB::table('forecast_actuals')->select('part_number', 'periode', 'forecast_actual')->get();
            foreach ($faList as $fa) {
                $code = strtoupper(trim($fa->part_number ?? ''));
                $pStr = trim($fa->periode ?? '');
                if (empty($pStr)) continue;

                $period = strlen($pStr) === 7 ? $pStr : date('Y-m', strtotime($pStr));
                $qty    = (int) $fa->forecast_actual;

                if ($code && $qty > 0) {
                    if (!isset(static::$deliveryLogCache[$code][$period])) {
                        static::$deliveryLogCache[$code][$period] = $qty;
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 3. Actual
        try {
            $actList = \Illuminate\Support\Facades\DB::table('actuals')->select('part_number', 'periode', 'period_month', 'actual_qty')->get();
            foreach ($actList as $act) {
                $code = strtoupper(trim($act->part_number ?? ''));
                $pStr = trim($act->periode ?? $act->period_month ?? '');
                if (empty($pStr)) continue;

                $period = strlen($pStr) === 7 ? $pStr : date('Y-m', strtotime($pStr));
                $qty    = (int) $act->actual_qty;

                if ($code && $qty > 0) {
                    if (!isset(static::$deliveryLogCache[$code][$period])) {
                        static::$deliveryLogCache[$code][$period] = $qty;
                    }
                }
            }
        } catch (\Throwable $e) {}
    }

    private function initForecastingCache(): void
    {
        if (static::$forecastingCache !== null) return;

        static::$forecastingCache = [];
        $fcs = \Illuminate\Support\Facades\DB::table('forecastings')
            ->select('part_number', 'periode', 'period_month', 'forecast_qty')
            ->get();

        foreach ($fcs as $f) {
            $pn = strtoupper(trim($f->part_number ?? ''));
            if (empty($pn)) continue;

            $qty = (int) ($f->forecast_qty ?? 0);

            if (!empty($f->periode)) {
                $p = trim($f->periode);
                static::$forecastingCache[$pn][$p] = $qty;
            }
            if (!empty($f->period_month)) {
                $pm = trim($f->period_month);
                static::$forecastingCache[$pn][$pm] = $qty;
            }
        }
    }

    /**
     * Dapatkan nama bulan (cth: "JAN", "FEB", "MAR") untuk index bulan ke-$index (1-indexed).
     */
    public function getMonthName(int $index): string
    {
        $months = session('monitor_months');
        if (is_array($months) && isset($months[$index])) {
            return strtoupper(trim($months[$index]));
        }

        $startMonth = session('monitor_start_month', 'JUN');
        $allMonths  = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        $searchM    = strtoupper(trim($startMonth));
        if ($searchM === 'JULY') $searchM = 'JUL';

        $startIndex = array_search($searchM, $allMonths);
        if ($startIndex === false) {
            $startIndex = 5; // JUN default
        }

        return $allMonths[($startIndex + $index) % 12];
    }

    /**
     * Dapatkan nomor bulan 2-digit ("01", "02", dst.) untuk index bulan ke-$index.
     */
    public function getMonthNum(int $index): string
    {
        $mName = $this->getMonthName($index);
        $monthMap = [
            'JAN' => '01', 'FEB' => '02', 'MAR' => '03', 'APR' => '04',
            'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'JULY' => '07',
            'AUG' => '08', 'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DEC' => '12'
        ];
        return $monthMap[$mName] ?? sprintf('%02d', $index);
    }

    /**
     * Ambil PO dinamis dari Step 2 (Master PO) atau fallback ke kolom m{index}_po dari upload Excel.
     */
    public function getPoForMonth(int $index): int
    {
        if ($index <= 0) {
            return (int) ($this->m0_po ?? 0);
        }

        $itemKeys = array_unique(array_filter([$this->part_number, $this->drawing], fn($k) => !empty($k) && $k !== '-'));
        if (!empty($itemKeys)) {
            $period = $this->getPeriodForMonth($index);
            $this->initMasterPoCache();

            $sumQty = 0;
            $hasMasterPo = false;
            foreach ($itemKeys as $key) {
                $cleanKey = strtoupper(trim($key));
                if (isset(static::$masterPoCache[$cleanKey][$period])) {
                    $sumQty += static::$masterPoCache[$cleanKey][$period];
                    $hasMasterPo = true;
                }
            }
            if ($hasMasterPo) {
                return $sumQty;
            }
        }

        // Fallback: Ambil dari nilai yang diinput/diunggah di kolom m{i}_po
        return (int) ($this->{"m{$index}_po"} ?? 0);
    }

    /**
     * Ambil Delivery dari realisasi penerimaan (Step 3) atau fallback ke planned delivery / PO.
     */
    public function getDeliveryForMonth(int $index): int
    {
        if ($index <= 0) {
            return (int) ($this->m0_delivery ?? 0);
        }

        $itemKeys = array_unique(array_filter([$this->part_number, $this->drawing], fn($k) => !empty($k) && $k !== '-'));
        if (!empty($itemKeys)) {
            $period = $this->getPeriodForMonth($index);
            $this->initDeliveryLogCache();

            $sumQty = 0;
            $hasDeliveryLog = false;
            foreach ($itemKeys as $key) {
                $cleanKey = strtoupper(trim($key));
                if (isset(static::$deliveryLogCache[$cleanKey][$period])) {
                    $sumQty += static::$deliveryLogCache[$cleanKey][$period];
                    $hasDeliveryLog = true;
                }
            }
            if ($hasDeliveryLog) {
                return $sumQty;
            }
        }

        // Fallback: Ambil dari m{i}_delivery / m{i}_incoming jika ada, atau PO bulan tersebut (default rencana delivery)
        $directDel = $this->{"m{$index}_delivery"} ?? ($this->{"m{$index}_incoming"} ?? null);
        if ($directDel !== null && is_numeric($directDel)) {
            return (int) $directDel;
        }

        return $this->getPoForMonth($index);
    }

    /**
     * Outstanding Pre-Month untuk bulan ke-$index.
     */
    public function getOutstandingPreForMonth(int $index): int
    {
        if ($index <= 1) {
            return (int) ($this->plan_outstand ?? 0);
        }
        return $this->getOutstandingForMonth($index - 1);
    }

    /**
     * Dapatkan target Qty Forecast khusus untuk periode YYYY-MM secara presisi.
     */
    public function getForecastForPeriod(string $periodYYYYMM): int
    {
        if (empty($periodYYYYMM) || empty($this->part_number)) {
            return 0;
        }

        $cleanPart = strtoupper(trim($this->part_number));
        $this->initForecastingCache();

        if (isset(static::$forecastingCache[$cleanPart][$periodYYYYMM])) {
            return static::$forecastingCache[$cleanPart][$periodYYYYMM];
        }

        return 0;
    }

    /**
     * Dapatkan target Qty Forecast untuk bulan ke-$index.
     * Mengambil dari inputan Excel Step 1 (Forecast) / model Forecasting.
     */
    public function getForecastForMonth(int $index): int
    {
        if ($index <= 0) {
            return (int) ($this->m0_forecast ?? 0);
        }

        // 1. Ambil dari kolom m{index}_forecast jika diisi secara eksplisit
        $directFc = $this->{"m{$index}_forecast"} ?? null;
        if ($directFc !== null && (int)$directFc > 0) {
            return (int) $directFc;
        }

        // 2. Ambil dari kolom m{index}_po dari tabel PurchasingOutstanding (Step 1 Excel Import)
        if (isset($this->{"m{$index}_po"}) && $this->{"m{$index}_po"} !== null) {
            return (int) $this->{"m{$index}_po"};
        }

        // 3. Fallback ke m{index}_order_qty jika ada
        if (isset($this->{"m{$index}_order_qty"}) && $this->{"m{$index}_order_qty"} !== null) {
            return (int) $this->{"m{$index}_order_qty"};
        }

        return 0;
    }

    /**
     * Rumus Excel Outstanding: Outstanding = Outstanding (pre month) + PO - Delivery
     */
    public function getOutstandingForMonth(int $index): int
    {
        if ($index <= 0) {
            return (int) ($this->plan_outstand ?? 0);
        }
        $outPre = $this->getOutstandingPreForMonth($index);
        $po     = $this->getPoForMonth($index);
        $del    = $this->getDeliveryForMonth($index);
        return $outPre + $po - $del;
    }

    /**
     * Dapatkan nilai PROD untuk bulan ke-$index.
     * Rumus Excel: PROD = nilai yang diinput secara eksplisit di kolom m{i}_prod.
     * TIDAK fallback ke PO/Forecast agar perhitungan Stock & Ratio konsisten dengan Excel.
     */
    public function getProdForMonth(int $index): int
    {
        if ($index <= 0) {
            return (int) ($this->m0_prod ?? 0);
        }

        // Hanya ambil dari nilai yang tersimpan — tidak ada fallback otomatis
        return (int) ($this->{"m{$index}_prod"} ?? 0);
    }

    protected array $stockCalcCache = [];

    /**
     * Dapatkan Stock untuk bulan ke-$index (0..36).
     * Single Source of Truth:
     * - Month 0 (Pre-month): plan_stock
     * - Month 1..36: Menggunakan Stock langsung jika ada, atau kalkulasi (Prev Stock + Delivery - PROD).
     */
    public function getStockForMonth(int $index): int
    {
        if (isset($this->stockCalcCache[$index])) {
            return $this->stockCalcCache[$index];
        }

        if ($index <= 0) {
            return $this->stockCalcCache[0] = (int) ($this->plan_stock ?? 0);
        }

        // 1. Cek apakah ada nilai eksplisit m{i}_stock
        $directStock = $this->attributes["m{$index}_stock"] ?? null;
        if ($directStock !== null && is_numeric($directStock)) {
            return $this->stockCalcCache[$index] = (int) $directStock;
        }

        // 2. Kalkulasi berbasis transaksi: Stock = Prev Stock + Delivery - PROD
        $prevStock = $this->getStockForMonth($index - 1);
        $del  = $this->getDeliveryForMonth($index);
        $prod = $this->getProdForMonth($index);
        return $this->stockCalcCache[$index] = (int) ($prevStock + $del - $prod);
    }

    /**
     * Hitung Stock Amount ($ / Rp) = Stock Qty * Price
     */
    public function getStockAmountForMonth(int $index): float
    {
        $stockQty = $this->getStockForMonth($index);
        $priceVal = (float) $this->price;
        return (float) ($stockQty * $priceVal);
    }

    /**
     * Hitung Live Ratio (%) dinamis untuk bulan ke-$index:
     * Ratio (%) = (Current Stock Qty / Next Month Production Qty) * 100
     */
    public function getRatioForMonth(int $index): string
    {
        $stock    = $this->getStockForMonth($index);
        $nextProd = $this->getProdForMonth($index + 1);

        if ($nextProd <= 0) {
            return $stock === 0 ? '0%' : '#DIV/0!';
        }

        return round(($stock / $nextProd) * 100) . '%';
    }

    /**
     * Hitung Stock dinamis untuk bulan ke-$index (alias untuk konsistensi view)
     */
    public function getCalculatedStockForMonth(int $index): int
    {
        return $this->getStockForMonth($index);
    }

    /**
     * Legacy backward-compatibility: Mengarahkan Inventory langsung ke Stock (Single Source of Truth)
     */
    public function getInventoryForMonth(int $index): int
    {
        return $this->getStockForMonth($index);
    }

    /**
     * Tentukan class CSS badge untuk nilai ratio
     */
    public function getRatioBadgeClass(?string $ratio): string
    {
        if ($ratio === null || $ratio === '' || $ratio === '-' || $ratio === '#DIV/0!') {
            return 'bg-secondary bg-opacity-50 text-light border border-secondary px-2 py-1';
        }
        $num = (int) str_replace('%', '', $ratio);
        if ($num < 100) {
            return 'bg-danger text-white px-2 py-1';
        } elseif ($num > 200) {
            return 'bg-success text-white px-2 py-1';
        } else {
            return 'bg-warning text-dark px-2 py-1';
        }
    }

    /**
     * Magic getter untuk mendukung pemanggilan atribut dinamis mX_stock dan mX_ratio
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->getAttribute($key);
        }

        if (preg_match('/^m(\d+)_(stock|ratio|inventory)$/', $key, $matches)) {
            $idx = (int) $matches[1];
            if ($matches[2] === 'stock' || $matches[2] === 'inventory') {
                return $this->getStockForMonth($idx);
            } elseif ($matches[2] === 'ratio') {
                return $this->getRatioForMonth(max(0, $idx - 1));
            }
        }
        return parent::__get($key);
    }

    /**
     * JULY STOCK = plan_stock (stok awal, tidak ada PO/PROD untuk bulan pertama)
     * Sesuai format Excel KAWAI: bulan pertama hanya tampil STOCK
     */
    public function getM0StockAttribute(): int
    {
        return $this->getStockForMonth(0);
    }

    /**
     * RATIO setelah JULY = JULY STOCK / AUG PROD
     * Formula Excel: =+F5/I5 (JULY STOCK ÷ AUG PROD)
     * Menunjukkan berapa bulan stock JULY bisa menutupi produksi AUG
     */
    public function getM1RatioAttribute(): string
    {
        return $this->getRatioForMonth(0);
    }

    /**
     * AUG STOCK = JULY STOCK + AUG PO - AUG PROD
     * Formula Excel: =F5+H5-I5
     */
    public function getM1StockAttribute(): int
    {
        return $this->getStockForMonth(1);
    }

    /**
     * RATIO setelah AUG = AUG STOCK / SEP PROD
     * Formula Excel: =+J5/M5
     */
    public function getM2RatioAttribute(): string
    {
        return $this->getRatioForMonth(1);
    }

    /**
     * SEP STOCK = AUG STOCK + SEP PO - SEP PROD
     * Formula Excel: =J5+L5-M5
     */
    public function getM2StockAttribute(): int
    {
        return $this->getStockForMonth(2);
    }

    /**
     * RATIO setelah SEP = SEP STOCK / OCT PROD
     * Formula Excel: =+N5/Q5
     */
    public function getM3RatioAttribute(): string
    {
        return $this->getRatioForMonth(2);
    }

    /**
     * OCT STOCK = SEP STOCK + OCT PO - OCT PROD
     */
    public function getM3StockAttribute(): int
    {
        return $this->getStockForMonth(3);
    }

    /**
     * Hitung persentase pemenuhan order secara otomatis
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->order_qty <= 0) {
            return 0;
        }
        return round(($this->complete / $this->order_qty) * 100, 1);
    }

    /**
     * Helper untuk kelas badge status umum
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'Complete' => 'bg-success bg-opacity-25 text-success border border-success',
            'On Progress' => 'bg-warning bg-opacity-25 text-warning border border-warning',
            default => 'bg-secondary bg-opacity-25 text-light border border-secondary',
        };
    }

    /**
     * Label manusiawi untuk tahapan alur (Workflow Stage)
     */
    public function getWorkflowStageLabelAttribute(): string
    {
        return match($this->workflow_stage) {
            'waiting_manager'  => '1. Menunggu Approval Manager',
            'revision_manager' => '1. Revisi PO (Ditolak Manager)',
            'approved_manager' => '2. PO Disetujui Manager (Siap Kirim)',
            'waiting_supplier' => '2. Menunggu Konfirmasi Supplier',
            'revision_supplier'=> '2. Revisi PO (Ditolak Supplier)',
            'material_shipped' => '3. Material Dikirim Supplier',
            'iad_check'        => '4. Check Delivery & Pelaksanaan IAD',
            'iad_rejected'     => '4. Gagal IAD (Kirim Ulang Material!)',
            'completed'        => '5. Lolos IAD (Material Masuk Gudang)',
            default            => '1. Menunggu Approval Manager',
        };
    }

    /**
     * Kelas styling badge untuk tahapan alur PO
     */
    public function getWorkflowStageBadgeAttribute(): string
    {
        return match($this->workflow_stage) {
            'waiting_manager'  => 'bg-warning bg-opacity-25 text-warning border border-warning',
            'revision_manager' => 'bg-danger bg-opacity-25 text-danger border border-danger',
            'approved_manager' => 'bg-info bg-opacity-25 text-info border border-info',
            'waiting_supplier' => 'bg-primary bg-opacity-25 text-light border border-primary',
            'revision_supplier'=> 'bg-danger bg-opacity-25 text-danger border border-danger',
            'material_shipped' => 'bg-info bg-opacity-25 text-info border border-info',
            'iad_check'        => 'bg-warning bg-opacity-25 text-warning border border-warning',
            'iad_rejected'     => 'bg-danger bg-opacity-25 text-danger border border-danger fw-bold',
            'completed'        => 'bg-success bg-opacity-25 text-success border border-success fw-bold',
            default            => 'bg-secondary bg-opacity-25 text-light border border-secondary',
        };
    }

    /**
     * Nomor urut step alur (1 sampai 5) untuk render Progress Stepper
     */
    public function getWorkflowStepNumberAttribute(): int
    {
        return match($this->workflow_stage) {
            'waiting_manager', 'revision_manager' => 1,
            'approved_manager', 'waiting_supplier', 'revision_supplier' => 2,
            'material_shipped' => 3,
            'iad_check', 'iad_rejected' => 4,
            'completed' => 5,
            default => 1,
        };
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            $itemCode = strtoupper(trim($model->part_number ?: $model->drawing));
            $poNum    = strtoupper(trim($model->po_number ?: $model->part_number));

            if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_forecast_actuals')) {
                $po = (int) $model->order_qty;
                // Jika ada record khusus di tabel outstandings pada bulan aktif, pakai itu. Jika tidak, pakai po - complete
                $currentPeriod = date('Y-m');
                $outstandingRecord = \Illuminate\Support\Facades\Schema::hasTable('outstandings')
                    ? \App\Models\Outstanding::where('part_number', $itemCode)
                        ->where(function($q) use ($currentPeriod) {
                            $q->where('periode', $currentPeriod)->orWhere('period_month', $currentPeriod);
                        })->first()
                    : null;

                $outstanding = $outstandingRecord ? (int)$outstandingRecord->outstanding_qty : (int)max(0, $po - ($model->complete ?? 0));
                $forecastActual = $po - $outstanding;

                \App\Models\ForecastActual::updateOrCreate(
                    [
                        'part_number' => $itemCode,
                        'periode'     => $currentPeriod,
                    ],
                    [
                        'description'     => $model->description ?? '-',
                        'po'              => $po,
                        'forecast_actual' => $forecastActual,
                    ]
                );
            }

            // Sync ke table outstandings & forecastings (Untuk Modul Master Forecast Step 1 & Analisis Komparasi Step 4)
            $firstMonthProd = (int) ($model->m1_prod ?? $model->production_qty ?? 0);
            $firstMonthPo   = (int) ($model->m1_po ?? $model->order_qty ?? 0);
            $currentPeriod  = session('monitor_m0', now()->format('Y-m'));

            // 1 Part Number = 1 Consolidated Record di Master Forecast (Update in place, tanpa bikin duplikat)
            \App\Models\Forecasting::updateOrCreate(
                [
                    'part_number'  => $itemCode,
                    'period_month' => $currentPeriod,
                ],
                [
                    'user_id'         => $model->user_id ?? auth()->id(),
                    'description'     => $model->description ?? '-',
                    'outstanding_pre' => (int) ($model->plan_outstand ?? 0),
                    'stock_pre'       => (int) ($model->plan_stock ?? 0),
                    'po'              => $firstMonthPo,
                    'po_qty'          => $firstMonthPo,
                    'forecast_qty'    => $firstMonthProd > 0 ? $firstMonthProd : max(0, $firstMonthPo),
                    'production'      => $firstMonthProd,
                    'production_qty'  => $firstMonthProd,
                    'stock'           => (int) ($model->plan_stock ?? 0),
                    'stock_qty'       => (int) ($model->plan_stock ?? 0),
                    'periode'         => $currentPeriod,
                    'period_month'    => $currentPeriod,
                ]
            );

            // Sync ke Outstanding Master
            if (\Illuminate\Support\Facades\Schema::hasTable('outstandings')) {
                \App\Models\Outstanding::updateOrCreate(
                    [
                        'part_number' => $itemCode,
                    ],
                    [
                        'po'              => $poNum,
                        'outstanding_qty' => (int) ($model->plan_outstand ?? 0),
                        'periode'         => $currentPeriod,
                        'period_month'    => $currentPeriod,
                    ]
                );
            }
        });

        static::deleted(function ($model) {
            $partNumber = strtoupper(trim($model->part_number));
            $itemCode   = ($model->drawing && $model->drawing !== '-') ? strtoupper(trim($model->drawing)) : null;
            
            $keys = array_filter([$partNumber, $itemCode]);
            if (!empty($keys)) {
                if (\Illuminate\Support\Facades\Schema::hasTable('outstandings')) {
                    \App\Models\Outstanding::whereIn('part_number', $keys)->delete();
                }
                \App\Models\Forecasting::whereIn('part_number', $keys)->delete();
                \App\Models\Actual::whereIn('part_number', $keys)->delete();
                \App\Models\ForecastActual::whereIn('part_number', $keys)->delete();
                \App\Models\ComparisonMaster::whereIn('part_number', $keys)->delete();
            }
        });
    }
}
