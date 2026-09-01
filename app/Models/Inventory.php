<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SafeSchemaModelTrait;

class Inventory extends Model
{
    use HasFactory, SafeSchemaModelTrait;

    protected $table = 'inventories';

    protected $fillable = [
        'tanggal_inventory',
        'part_number',
        'drawing',
        'description',
        'supplier_code',
        'supplier_name',
        'category_id',
        'factory_code',
        'm0_inventory',
        'current_stock',
        'min_stock',
        'max_stock',
        'unit_measure',
        'unit_price',
        'currency',
        'warehouse_location',
        'status',
        'user_id',
    ];

    public function __construct(array $attributes = [])
    {
        // Dynamically append m1_inventory through m36_inventory to fillable
        for ($i = 1; $i <= 36; $i++) {
            $this->fillable[] = "m{$i}_inventory";
        }
        parent::__construct($attributes);
    }

    /**
     * Relasi ke Kategori Purchasing.
     */
    public function category()
    {
        return $this->belongsTo(PurchasingCategory::class, 'category_id');
    }

    /**
     * Relasi ke User / PIC.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke PurchasingOutstanding berdasarkan part_number.
     */
    public function purchasingOutstanding()
    {
        return $this->hasOne(PurchasingOutstanding::class, 'part_number', 'part_number');
    }

    /**
     * Dapatkan stok inventori fisik untuk bulan ke-$index (0-36).
     */
    public function getInventoryForMonth(int $index): int
    {
        if ($index <= 0) {
            return (int) ($this->m0_inventory ?? 0);
        }

        $directInv = $this->{"m{$index}_inventory"} ?? null;
        if ($directInv !== null && (int)$directInv > 0) {
            return (int) $directInv;
        }

        // Fallback: Calculate running inventory using PurchasingOutstanding if available
        $poModel = $this->purchasingOutstanding;
        if ($poModel) {
            return $poModel->getStockForMonth($index);
        }

        return (int) ($this->current_stock ?? 0);
    }

    /**
     * Total nilai inventori fisik dalam USD.
     */
    public function getTotalValueUsdAttribute(): float
    {
        $stk = $this->current_stock > 0 ? $this->current_stock : $this->m0_inventory;
        $prc = (float) $this->unit_price;

        if (strtoupper($this->currency) === 'IDR') {
            return $prc > 0 ? ($stk * $prc) / 16500 : 0.0;
        }
        return $stk * $prc;
    }

    /**
     * Total nilai inventori fisik dalam IDR.
     */
    public function getTotalValueIdrAttribute(): float
    {
        $stk = $this->current_stock > 0 ? $this->current_stock : $this->m0_inventory;
        $prc = (float) $this->unit_price;

        if (strtoupper($this->currency) === 'IDR') {
            return $stk * $prc;
        }
        return $stk * $prc * 16500;
    }

    /**
     * Status stok inventori (OPTIMAL, DEFICIT, OVERSTOCK).
     */
    public function getStockStatusAttribute(): string
    {
        $stk = $this->current_stock > 0 ? $this->current_stock : $this->m0_inventory;
        $min = (int) $this->min_stock;
        $max = (int) $this->max_stock;

        if ($min > 0 && $stk < $min) {
            return 'DEFICIT';
        }
        if ($max > 0 && $stk > $max) {
            return 'OVERSTOCK';
        }
        return 'OPTIMAL';
    }
}
