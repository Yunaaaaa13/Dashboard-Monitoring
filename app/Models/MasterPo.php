<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SafeSchemaModelTrait;
use App\Services\DataValidation\DatabaseSchemaManager;

class MasterPo extends Model
{
    use HasFactory, SafeSchemaModelTrait;

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

    public static function ensureSchemaIntegrity(): void
    {
        DatabaseSchemaManager::ensureMasterPosTable();
    }

    protected static function booted()
    {
        static::ensureSchemaIntegrity();
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
