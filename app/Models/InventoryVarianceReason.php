<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryVarianceReason extends Model
{
    use HasFactory;

    protected $table = 'inventory_variance_reasons';

    protected $fillable = [
        'part_number',
        'variance_type',
        'reason_category',
        'reason_notes',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
