<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColumnAlias extends Model
{
    use HasFactory;

    protected $fillable = [
        'raw_name', 'canonical_field', 'usage_count'
    ];

    public static function incrementUsage(string $rawName, string $canonicalField): void
    {
        $alias = self::firstOrCreate(
            ['raw_name' => mb_strtoupper($rawName), 'canonical_field' => $canonicalField],
            ['usage_count' => 0]
        );
        
        $alias->increment('usage_count');
    }
}
