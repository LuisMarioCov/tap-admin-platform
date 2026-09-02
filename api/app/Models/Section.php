<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Section extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'sections';

    protected $fillable = [
        'key',
        'name',
        'route',
    ];

    /** @return list<string> */
    public static function allowedKeys(): array
    {
        return static::query()->pluck('key')->all();
    }
}
