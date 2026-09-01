<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPo extends Model
{
    use HasFactory;

    protected $table = 'master_pos';

    protected $fillable = [
        'tanggal',
        'supplier',
        'po',
        'item_code',
        'factory_code',
        'name',
        'qty',
        'price',
        'currency',
        'created_by',
        'user_id',
        'delivery_category_code',
        'category_id',
    ];

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
            if (\Illuminate\Support\Facades\Schema::hasTable('master_pos')) {
                $cols = static::getTableColumns();
                $missingCols = [];
                foreach (['category_id', 'factory_code', 'delivery_category_code', 'price', 'currency', 'user_id', 'created_by'] as $c) {
                    if (!in_array($c, $cols, true)) {
                        $missingCols[] = $c;
                    }
                }

                if (!empty($missingCols)) {
                    \Illuminate\Support\Facades\Schema::table('master_pos', function (\Illuminate\Database\Schema\Blueprint $table) use ($missingCols) {
                        if (in_array('category_id', $missingCols, true)) {
                            $table->unsignedBigInteger('category_id')->nullable()->index();
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
                        if (in_array('user_id', $missingCols, true)) {
                            $table->unsignedBigInteger('user_id')->nullable();
                        }
                        if (in_array('created_by', $missingCols, true)) {
                            $table->unsignedBigInteger('created_by')->nullable();
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
    }

    public function category()
    {
        return $this->belongsTo(PurchasingCategory::class, 'category_id');
    }

    public function getCategoryAttribute($value)
    {
        if ($this->relationLoaded('category') && $this->getRelation('category')) {
            return $this->getRelation('category');
        }

        if (!empty($this->category_id)) {
            $cat = PurchasingCategory::find($this->category_id);
            if ($cat) return $cat;
        }

        // Fallback pencarian kategori berdasarkan Item Code
        $code = strtoupper(trim((string)$this->item_code));
        if ($code) {
            $poItem = PurchasingOutstanding::where('part_number', $code)->orWhere('drawing', $code)->first();
            if ($poItem && $poItem->category) {
                return $poItem->category;
            }
        }

        return null;
    }

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
}
