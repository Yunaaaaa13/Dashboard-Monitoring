<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename', 'original_filename', 'file_hash', 'file_size',
        'document_type', 'document_type_confidence', 'detected_header_row',
        'total_rows', 'total_columns', 'date_range_min', 'date_range_max',
        'unique_items', 'unique_pos', 'unique_suppliers',
        'currency_distribution', 'profile_data', 'uploaded_by', 'status'
    ];

    protected $casts = [
        'currency_distribution' => 'array',
        'profile_data' => 'array',
    ];

    public function importSessions(): HasMany
    {
        return $this->hasMany(ImportSession::class);
    }

    public function columnMappings(): HasMany
    {
        return $this->hasMany(ColumnMapping::class);
    }

    public function qualityIssues(): HasMany
    {
        return $this->hasMany(DataQualityIssue::class);
    }

    public function reconciliationResults(): HasMany
    {
        return $this->hasMany(ReconciliationResult::class);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('document_type', $type);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'COMPLETED');
    }

    public function scopeByHash(Builder $query, string $hash): Builder
    {
        return $query->where('file_hash', $hash);
    }
}
