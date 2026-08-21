<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'line_code',
        'line_name',
        'product_category',
        'supervisor',
        'daily_target_capacity',
        'status',
    ];

    public function logs()
    {
        return $this->hasMany(ProductionLog::class);
    }
}
