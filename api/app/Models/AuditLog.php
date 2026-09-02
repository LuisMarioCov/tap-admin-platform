<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $connection = 'mongodb';

    protected $collection = 'audit_logs';

    protected $fillable = [
        'entity',
        'entity_id',
        'action',
        'before',
        'after',
        'user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'before' => 'array',
            'after' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
