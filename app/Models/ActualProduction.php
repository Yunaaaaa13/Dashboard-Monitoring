<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActualProduction extends Model
{
    use HasFactory;

    protected $table = 'actual_productions';

    protected $fillable = [
        'tanggal_produksi',
        'item_code',
        'supplier_code',
        'supplier_name',
        'description',
        'factory_code',
        'qty',
        'currency',
        'user_id',
        'delivery_category_code',
        'import_batch_id',
        'excel_row_number',
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
}
