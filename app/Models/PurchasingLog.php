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

    protected static function booted()
    {
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
