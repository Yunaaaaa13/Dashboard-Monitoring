<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataChangeLog extends Model
{
    use HasFactory;

    protected $table = 'data_change_logs';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'field',
        'old_value',
        'new_value',
        'changed_by',
        'reason',
        'ip_address',
    ];
}
