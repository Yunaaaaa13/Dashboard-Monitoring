<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryCategory extends Model
{
    use HasFactory;

    protected $table = 'delivery_categories';

    protected $fillable = [
        'code',
        'name',
        'description',
        'currency',
    ];

    /**
     * Map default badge styling berdasarkan kode kategori pengantaran
     */
    public function getBadgeClassAttribute(): string
    {
        return match (strtoupper($this->code)) {
            'IMP', 'IMPORT' => 'bg-info bg-opacity-25 text-info border border-info',
            'LOC', 'LOCAL'  => 'bg-success bg-opacity-25 text-success border border-success',
            'CON', 'CONSUMABLE' => 'bg-warning bg-opacity-25 text-warning border border-warning',
            default => 'bg-secondary bg-opacity-25 text-light border border-secondary',
        };
    }
}
