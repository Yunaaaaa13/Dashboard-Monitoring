<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id', 'session_code', 'status',
        'total_rows', 'valid_count', 'warning_count', 'error_count',
        'duplicate_count', 'inserted_po_count', 'inserted_incoming_count',
        'imported_by', 'started_at', 'completed_at', 'error_message', 'metadata'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function qualityIssues(): HasMany
    {
        return $this->hasMany(DataQualityIssue::class);
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => 'COMPLETED',
            'completed_at' => now(),
        ]);
    }

    public function markFailed(string $message): void
    {
        $this->update([
            'status' => 'FAILED',
            'error_message' => $message,
            'completed_at' => now(),
        ]);
    }

    public function isComplete(): bool
    {
        return $this->status === 'COMPLETED';
    }
}
