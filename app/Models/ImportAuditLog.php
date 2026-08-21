<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportAuditLog extends Model
{
    use HasFactory;

    protected $table = 'import_audit_logs';

    protected $fillable = [
        'batch_id',
        'row_number',
        'field',
        'input_value',
        'error_type',
        'severity',
        'error_message',
        'suggestion',
        'is_resolved',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'batch_id', 'batch_id');
    }
}
