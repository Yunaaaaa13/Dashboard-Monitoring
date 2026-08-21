<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ColumnMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id', 'source_column_letter', 'source_column_name',
        'canonical_field', 'confidence', 'mapping_method', 'confirmed_by_user'
    ];

    protected $casts = [
        'confirmed_by_user' => 'boolean',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('confirmed_by_user', true);
    }
}
