<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class DataQualityIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id', 'import_session_id', 'row_number', 'column_name',
        'issue_type', 'severity', 'message', 'raw_value'
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function importSession(): BelongsTo
    {
        return $this->belongsTo(ImportSession::class);
    }

    public function scopeErrors(Builder $query): Builder
    {
        return $query->where('severity', 'ERROR');
    }

    public function scopeWarnings(Builder $query): Builder
    {
        return $query->where('severity', 'WARNING');
    }
}
