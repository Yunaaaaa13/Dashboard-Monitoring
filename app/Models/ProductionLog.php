<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_line_id',
        'ezrunner_batch_id',
        'log_time',
        'target_output',
        'actual_output',
        'defect_count',
        'status_note',
    ];

    protected $casts = [
        'log_time' => 'datetime',
    ];

    public function line()
    {
        return $this->belongsTo(ProductionLine::class, 'production_line_id');
    }
}
