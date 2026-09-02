<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Counter extends Model
{
    public $timestamps = false;

    protected $connection = 'mongodb';

    protected $collection = 'counters';

    protected $primaryKey = '_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        '_id',
        'seq',
    ];

    protected function casts(): array
    {
        return [
            'seq' => 'integer',
        ];
    }
}
