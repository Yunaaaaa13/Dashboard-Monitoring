<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    use HasFactory;

    protected $table = 'import_batches';

    protected $fillable = [
        'batch_id',
        'template_type',
        'template_version',
        'file_name',
        'file_hash',
        'uploaded_by',
        'total_rows',
        'valid_rows',
        'warning_rows',
        'rejected_rows',
        'duplicate_rows',
        'total_qty_source',
        'total_qty_imported',
        'reconciliation_diff',
        'reconciliation_status',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'total_qty_source' => 'decimal:4',
        'total_qty_imported' => 'decimal:4',
        'reconciliation_diff' => 'decimal:4',
    ];

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ImportAuditLog::class, 'batch_id', 'batch_id');
    }
}
