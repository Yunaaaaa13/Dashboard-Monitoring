<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasingCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_code',
        'category_name',
        'pic_buyer',
        'buyer_user_id',
        'monthly_target_units',
        'target_qty',
        'unit_price',
        'status',
    ];

    protected $casts = [
        'monthly_target_units' => 'decimal:2',
        'target_qty' => 'integer',
        'unit_price' => 'decimal:4',
    ];

    public function logs()
    {
        return $this->hasMany(PurchasingLog::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    public function getTargetUsdAttribute(): float
    {
        return (float) ($this->monthly_target_units ?? 0);
    }

    public function getFormattedTargetUsdAttribute(): string
    {
        return '$ ' . number_format($this->target_usd, 2);
    }
}

