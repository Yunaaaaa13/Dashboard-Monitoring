<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ReconciliationResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id', 'import_session_id', 'item_code', 'po_number',
        'supplier', 'period', 'po_qty', 'received_qty', 'outstanding_qty',
        'fulfillment_pct', 'po_amount_usd', 'received_amount_usd',
        'match_level', 'match_confidence', 'status'
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function importSession(): BelongsTo
    {
        return $this->belongsTo(ImportSession::class);
    }

    public function scopeOverReceived(Builder $query): Builder
    {
        return $query->where('status', 'OVER_RECEIVED');
    }

    public function scopePartial(Builder $query): Builder
    {
        return $query->where('status', 'PARTIAL');
    }

    public function scopeUnmatched(Builder $query): Builder
    {
        return $query->where('status', 'UNMATCHED_PO');
    }
}
