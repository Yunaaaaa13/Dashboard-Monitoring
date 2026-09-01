<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchasing_category_id',
        'user_id',
        'receipt_date',
        'item_code',
        'factory_code',
        'item_name',
        'supplier_name',
        'po_reference',
        'period_month',
        'target_order',
        'actual_received',
        'price',
        'currency',
        'amount',
        'production_qty',
        'pending_order',
        'status_note',
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

    public function category()
    {
        return $this->belongsTo(PurchasingCategory::class, 'purchasing_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static ?array $tableColumnsCache = null;

    public static function getTableColumns(): array
    {
        if (static::$tableColumnsCache === null) {
            try {
                static::$tableColumnsCache = \Illuminate\Support\Facades\Schema::getColumnListing((new static)->getTable());
            } catch (\Throwable $e) {
                static::$tableColumnsCache = [];
            }
        }
        return static::$tableColumnsCache;
    }

    public static function clearColumnsCache(): void
    {
        static::$tableColumnsCache = null;
    }

    public static function ensureSchemaIntegrity(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_logs')) {
                $cols = static::getTableColumns();
                $missingCols = [];
                foreach (['purchasing_category_id', 'factory_code', 'delivery_category_code', 'price', 'currency', 'amount', 'production_qty', 'pending_order', 'user_id'] as $c) {
                    if (!in_array($c, $cols, true)) {
                        $missingCols[] = $c;
                    }
                }

                if (!empty($missingCols)) {
                    \Illuminate\Support\Facades\Schema::table('purchasing_logs', function (\Illuminate\Database\Schema\Blueprint $table) use ($missingCols) {
                        if (in_array('purchasing_category_id', $missingCols, true)) {
                            $table->unsignedBigInteger('purchasing_category_id')->nullable()->index();
                        }
                        if (in_array('factory_code', $missingCols, true)) {
                            $table->string('factory_code', 50)->default('KIP 1')->index();
                        }
                        if (in_array('delivery_category_code', $missingCols, true)) {
                            $table->string('delivery_category_code', 20)->default('LOC');
                        }
                        if (in_array('price', $missingCols, true)) {
                            $table->decimal('price', 15, 4)->default(0);
                        }
                        if (in_array('currency', $missingCols, true)) {
                            $table->string('currency', 10)->default('USD');
                        }
                        if (in_array('amount', $missingCols, true)) {
                            $table->decimal('amount', 18, 4)->default(0);
                        }
                        if (in_array('production_qty', $missingCols, true)) {
                            $table->integer('production_qty')->default(0);
                        }
                        if (in_array('pending_order', $missingCols, true)) {
                            $table->integer('pending_order')->default(0);
                        }
                        if (in_array('user_id', $missingCols, true)) {
                            $table->unsignedBigInteger('user_id')->nullable();
                        }
                    });
                    static::clearColumnsCache();
                }
            }
        } catch (\Throwable $e) {
            // Silently allow graceful fallback
        }
    }

    protected static function booted()
    {
        static::ensureSchemaIntegrity();

        static::saving(function ($model) {
            $cols = static::getTableColumns();
            if (!empty($cols)) {
                foreach ($model->attributes as $key => $val) {
                    if (!in_array($key, $cols, true)) {
                        unset($model->attributes[$key]);
                    }
                }
            }
        });

        static::deleted(function ($model) {
            $partNumber = strtoupper(trim($model->item_code ?: ($model->po_reference ?: '')));
            if (!empty($model->po_reference) && empty($model->item_code)) {
                $cleanRef = preg_replace('/^PO-/i', '', trim($model->po_reference));
                $poMaster = \App\Models\PurchasingOutstanding::where('po_number', $model->po_reference)
                    ->orWhere('part_number', $cleanRef)
                    ->orWhere('drawing', $model->po_reference)
                    ->first();
                if ($poMaster) {
                    $partNumber = strtoupper(trim($poMaster->part_number ?: $poMaster->drawing));
                }
            }

            if (!empty($partNumber)) {
                $periode = $model->period_month ?: date('Y-m');
                \App\Models\Actual::where('part_number', $partNumber)
                    ->where(function ($q) use ($periode) {
                        $q->where('periode', $periode)->orWhere('period_month', $periode);
                    })->delete();

                \App\Models\ComparisonMaster::syncDelete($partNumber, $periode);
            }
        });
    }
}
